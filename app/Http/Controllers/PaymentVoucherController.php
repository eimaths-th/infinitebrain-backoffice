<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherItem;
use App\Models\PaymentVoucherLog;
use App\Models\PaymentVoucherAttachment;
use App\Exports\PaymentVoucherExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PaymentVoucherController extends Controller
{
    /**
     * แสดงรายการใบสำคัญจ่ายทั้งหมด
     */
    public function index(Request $request)
    {
        $query = PaymentVoucher::with(['items', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->to_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%$search%")
                    ->orWhere('pay_to', 'like', "%$search%");
            });
        }

        if (Auth::user()->level == 3) {
            $query->where('created_by', Auth::id());
        }

        if (Auth::user()->hasRole('account')) {
            $query->where('created_by', Auth::id());
        }

        $vouchers = $query->paginate(20);

        return view('payment_voucher.index', compact('vouchers'));
    }

    /**
     * แสดงฟอร์มสร้างใบสำคัญจ่ายใหม่
     */
    public function create()
    {
        $branchCodes = PaymentVoucher::$branchCodes;
        $voucherNo = PaymentVoucher::generateVoucherNo();
        return view('payment_voucher.create', compact('voucherNo', 'branchCodes'));
    }

    /**
     * บันทึกใบสำคัญจ่ายใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_code' => 'required|string|max:10',
            'voucher_date' => 'required|date',
            'pay_to' => 'required|string|max:255',
            'pay_to_address' => 'nullable|string',
            'pay_to_account_no' => 'nullable|string|max:50',
            'pay_to_bank_name' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'voucher_attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
            'items.*.attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $branchCode = $request->branch_code ?? 'EL';
            
            // บันทึกหรืออัปเดตข้อมูลผู้รับเงิน (Payee)
            $payee = null;
            if ($request->pay_to) {
                $payee = \App\Models\Payee::findOrCreateByName($request->pay_to, [
                    'address' => $request->pay_to_address,
                    'account_no' => $request->pay_to_account_no,
                    'bank_name' => $request->pay_to_bank_name,
                    'created_by' => Auth::id(),
                ]);
            }
            
            // สร้างใบสำคัญจ่าย
            $voucher = PaymentVoucher::create([
                'voucher_no' => PaymentVoucher::generateVoucherNo($branchCode),
                'branch_code' => $branchCode,
                'voucher_date' => $request->voucher_date,
                'pay_to' => $request->pay_to,
                'pay_to_address' => $request->pay_to_address,
                'pay_to_account_no' => $request->pay_to_account_no,
                'pay_to_bank_name' => $request->pay_to_bank_name,
                'payee_id' => $payee?->id,
                'remark' => $request->remark,
                'status' => $request->submit ? PaymentVoucher::STATUS_PENDING : PaymentVoucher::STATUS_DRAFT,
                'created_by' => Auth::id(),
                'created_by_name' => Auth::user()->name ?? 'System',
            ]);

            // อัพโหลดไฟล์แนบของ Voucher
            if ($request->hasFile('voucher_attachments')) {
                foreach ($request->file('voucher_attachments') as $file) {
                    $this->storeAttachment($file, $voucher->id, null, PaymentVoucherAttachment::TYPE_VOUCHER);
                }
            }

            // สร้างรายการย่อย
            $totalAmount = 0;
            foreach ($request->items as $index => $item) {
                if (!empty($item['description']) && $item['amount'] > 0) {
                    $voucherItem = PaymentVoucherItem::create([
                        'payment_voucher_id' => $voucher->id,
                        'item_no' => $index + 1,
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                    ]);
                    $totalAmount += $item['amount'];

                    // อัพโหลดไฟล์แนบของ Item
                    if (isset($item['attachments']) && is_array($item['attachments'])) {
                        foreach ($item['attachments'] as $file) {
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $this->storeAttachment($file, $voucher->id, $voucherItem->id, PaymentVoucherAttachment::TYPE_ITEM);
                            }
                        }
                    }
                }
            }

            // อัพเดทยอดรวม
            $voucher->update(['total_amount' => $totalAmount]);

            // บันทึก Log
            PaymentVoucherLog::create([
                'payment_voucher_id' => $voucher->id,
                'action' => PaymentVoucherLog::ACTION_CREATE,
                'from_status' => null,
                'to_status' => $voucher->status,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
            ]);

            if ($request->submit) {
                PaymentVoucherLog::create([
                    'payment_voucher_id' => $voucher->id,
                    'action' => PaymentVoucherLog::ACTION_SUBMIT,
                    'from_status' => PaymentVoucher::STATUS_DRAFT,
                    'to_status' => PaymentVoucher::STATUS_PENDING,
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? 'System',
                ]);
            }

            DB::commit();

            return redirect()->route('payment-voucher.show', $voucher->id)
                ->with('success', 'สร้างใบสำคัญจ่ายเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * แสดงรายละเอียดใบสำคัญจ่าย
     */
    public function show($id)
    {
        $voucher = PaymentVoucher::with(['items.attachments', 'attachments', 'logs'])->findOrFail($id);
        return view('payment_voucher.show', compact('voucher'));
    }

    /**
     * แสดงฟอร์มแก้ไขใบสำคัญจ่าย
     */
    public function edit($id)
    {
        $voucher = PaymentVoucher::with(['items.attachments', 'attachments'])->findOrFail($id);

        if (!$voucher->canEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบสำคัญจ่ายนี้ได้');
        }

        $branchCodes = PaymentVoucher::$branchCodes;
        return view('payment_voucher.edit', compact('voucher', 'branchCodes'));
    }

    /**
     * อัพเดทใบสำคัญจ่าย
     */
    public function update(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบสำคัญจ่ายนี้ได้');
        }

        $request->validate([
            'voucher_date' => 'required|date',
            'pay_to' => 'required|string|max:255',
            'pay_to_address' => 'nullable|string',
            'pay_to_account_no' => 'nullable|string|max:50',
            'pay_to_bank_name' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'voucher_attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
            'items.*.attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $voucher->status;

            // อัปเดตข้อมูลผู้รับเงิน (Payee) ถ้ามี
            if ($request->pay_to) {
                $payee = \App\Models\Payee::findOrCreateByName($request->pay_to, [
                    'address' => $request->pay_to_address,
                    'account_no' => $request->pay_to_account_no,
                    'bank_name' => $request->pay_to_bank_name,
                    'created_by' => Auth::id(),
                ]);
            }

            // อัพเดทข้อมูลหลัก
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'pay_to' => $request->pay_to,
                'pay_to_address' => $request->pay_to_address,
                'pay_to_account_no' => $request->pay_to_account_no,
                'pay_to_bank_name' => $request->pay_to_bank_name,
                'payee_id' => $payee->id ?? $voucher->payee_id,
                'remark' => $request->remark,
                'status' => $request->submit ? PaymentVoucher::STATUS_PENDING : PaymentVoucher::STATUS_DRAFT,
            ]);

            // อัพโหลดไฟล์แนบของ Voucher (เพิ่มใหม่)
            if ($request->hasFile('voucher_attachments')) {
                foreach ($request->file('voucher_attachments') as $file) {
                    $this->storeAttachment($file, $voucher->id, null, PaymentVoucherAttachment::TYPE_VOUCHER);
                }
            }

            // ลบรายการเดิมและ attachments ของ items (cascade)
            $voucher->items()->delete();

            $totalAmount = 0;
            foreach ($request->items as $index => $item) {
                if (!empty($item['description']) && $item['amount'] > 0) {
                    $voucherItem = PaymentVoucherItem::create([
                        'payment_voucher_id' => $voucher->id,
                        'item_no' => $index + 1,
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                    ]);
                    $totalAmount += $item['amount'];

                    // อัพโหลดไฟล์แนบของ Item
                    if (isset($item['attachments']) && is_array($item['attachments'])) {
                        foreach ($item['attachments'] as $file) {
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $this->storeAttachment($file, $voucher->id, $voucherItem->id, PaymentVoucherAttachment::TYPE_ITEM);
                            }
                        }
                    }
                }
            }

            $voucher->update(['total_amount' => $totalAmount]);

            // บันทึก Log
            PaymentVoucherLog::create([
                'payment_voucher_id' => $voucher->id,
                'action' => PaymentVoucherLog::ACTION_UPDATE,
                'from_status' => $oldStatus,
                'to_status' => $voucher->status,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
            ]);

            if ($request->submit && $oldStatus !== PaymentVoucher::STATUS_PENDING) {
                PaymentVoucherLog::create([
                    'payment_voucher_id' => $voucher->id,
                    'action' => PaymentVoucherLog::ACTION_SUBMIT,
                    'from_status' => $oldStatus,
                    'to_status' => PaymentVoucher::STATUS_PENDING,
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? 'System',
                ]);
            }

            DB::commit();

            return redirect()->route('payment-voucher.show', $voucher->id)
                ->with('success', 'อัพเดทใบสำคัญจ่ายเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ลบใบสำคัญจ่าย
     */
    public function destroy($id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canEdit()) {
            return back()->with('error', 'ไม่สามารถลบใบสำคัญจ่ายนี้ได้');
        }

        $voucher->delete();

        return redirect()->route('payment-voucher.index')
            ->with('success', 'ลบใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * ตรวจสอบใบสำคัญจ่าย (Reviewer)
     */
    public function review(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canReview()) {
            return back()->with('error', 'ไม่สามารถตรวจสอบใบสำคัญจ่ายนี้ได้');
        }

        $oldStatus = $voucher->status;

        $voucher->update([
            'status' => PaymentVoucher::STATUS_REVIEWED,
            'reviewed_by' => Auth::id(),
            'reviewed_by_name' => Auth::user()->name ?? 'System',
            'reviewed_at' => now(),
            'review_remark' => $request->remark,
        ]);

        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => PaymentVoucherLog::ACTION_REVIEW,
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_REVIEWED,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => $request->remark,
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'ตรวจสอบใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * อนุมัติใบสำคัญจ่าย (Approver)
     */
    public function approve(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canApprove()) {
            return back()->with('error', 'ไม่สามารถอนุมัติใบสำคัญจ่ายนี้ได้');
        }

        $oldStatus = $voucher->status;

        $voucher->update([
            'status' => PaymentVoucher::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_by_name' => Auth::user()->name ?? 'System',
            'approved_at' => now(),
            'approve_remark' => $request->remark,
        ]);

        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => PaymentVoucherLog::ACTION_APPROVE,
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_APPROVED,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => $request->remark,
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'อนุมัติใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * ปฏิเสธใบสำคัญจ่าย (Approver)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:500',
        ]);

        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canApprove()) {
            return back()->with('error', 'ไม่สามารถปฏิเสธใบสำคัญจ่ายนี้ได้');
        }

        $oldStatus = $voucher->status;

        $voucher->update([
            'status' => PaymentVoucher::STATUS_REJECTED,
            'approve_remark' => $request->remark,
        ]);

        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => PaymentVoucherLog::ACTION_REJECT,
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_REJECTED,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => $request->remark,
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'ปฏิเสธใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * เสร็จสิ้นใบสำคัญจ่าย
     */
    public function complete(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canComplete()) {
            return back()->with('error', 'ไม่สามารถดำเนินการได้');
        }

        $oldStatus = $voucher->status;

        $voucher->update([
            'status' => PaymentVoucher::STATUS_COMPLETED,
            'receiver_name' => $request->receiver_name,
            'received_date' => $request->received_date ?? now(),
        ]);

        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => PaymentVoucherLog::ACTION_COMPLETE,
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_COMPLETED,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => 'ผู้รับเงิน: ' . $request->receiver_name,
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'บันทึกเสร็จสิ้นเรียบร้อยแล้ว');
    }

    /**
     * รายการรอตรวจสอบ (สำหรับ Approver)
     */
    public function pendingList()
    {
        $vouchers = PaymentVoucher::where('status', PaymentVoucher::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('payment_voucher.pending', compact('vouchers'));
    }

    /**
     * รายการรอ Review (สำหรับ Reviewer)
     */
    public function approvedList()
    {
        $vouchers = PaymentVoucher::where('status', PaymentVoucher::STATUS_APPROVED)
            ->orderBy('approved_at', 'asc')
            ->paginate(20);

        return view('payment_voucher.approved', compact('vouchers'));
    }

    /**
     * Export to Excel
     */
    public function exportExcel($id)
    {
        $voucher = PaymentVoucher::with('items')->findOrFail($id);
        
        return Excel::download(
            new PaymentVoucherExport($voucher), 
            'payment_voucher_' . $voucher->voucher_no . '.xlsx'
        );
    }

    /**
     * บันทึกไฟล์แนบ
     */
    private function storeAttachment($file, $voucherId, $itemId = null, $type = 'voucher')
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('payment_vouchers/' . $voucherId, $fileName, 'public');

        return PaymentVoucherAttachment::create([
            'payment_voucher_id' => $voucherId,
            'payment_voucher_item_id' => $itemId,
            'attachment_type' => $type,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'uploaded_by_name' => Auth::user()->name ?? 'System',
        ]);
    }

    /**
     * ลบไฟล์แนบ
     */
    public function deleteAttachment($id)
    {
        $attachment = PaymentVoucherAttachment::findOrFail($id);
        $voucher = PaymentVoucher::find($attachment->payment_voucher_id);

        if (!$voucher || !$voucher->canEdit()) {
            return response()->json(['error' => 'ไม่สามารถลบไฟล์แนบได้'], 403);
        }

        // ลบไฟล์จาก storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'ลบไฟล์แนบเรียบร้อยแล้ว']);
    }

    /**
     * อัพโหลดไฟล์แนบเพิ่มเติม (AJAX)
     */
    public function uploadAttachment(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
            'type' => 'required|in:voucher,item',
            'item_id' => 'nullable|integer',
        ]);

        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canEdit()) {
            return response()->json(['error' => 'ไม่สามารถอัพโหลดไฟล์ได้'], 403);
        }

        $type = $request->type;
        $itemId = $request->item_id;

        $attachment = $this->storeAttachment($request->file('file'), $voucher->id, $itemId, $type);

        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'file_url' => $attachment->file_url,
                'file_size_formatted' => $attachment->file_size_formatted,
                'is_image' => $attachment->isImage(),
            ]
        ]);
    }

    /**
     * ค้นหาผู้รับเงิน (Payee) สำหรับ autocomplete
     */
    public function searchPayee(Request $request)
    {
        $keyword = $request->get('q', '');
        
        $payees = \App\Models\Payee::active()
            ->when($keyword, function ($query) use ($keyword) {
                $query->search($keyword);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'address', 'account_no', 'bank_name', 'tax_id', 'phone']);

        return response()->json($payees);
    }

    /**
     * ดึงเลขที่ใบสำคัญจ่ายถัดไปตามสาขา
     */
    public function getNextVoucherNo(Request $request)
    {
        $branchCode = $request->get('branch_code', 'EL');
        $voucherNo = PaymentVoucher::generateVoucherNo($branchCode);
        
        return response()->json([
            'voucher_no' => $voucherNo,
            'branch_code' => $branchCode,
        ]);
    }

    /**
     * บันทึกหรืออัปเดตข้อมูลผู้รับเงิน
     */
    public function savePayee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'account_no' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
        ]);

        $payee = \App\Models\Payee::findOrCreateByName($request->name, [
            'address' => $request->address,
            'account_no' => $request->account_no,
            'bank_name' => $request->bank_name,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'payee' => $payee,
        ]);
    }
}
