<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherItem;
use App\Models\PaymentVoucherLog;
use App\Models\PaymentVoucherAttachment;
use App\Exports\PaymentVoucherExport;
use App\Imports\PaymentVoucherImport;
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

        // ใช้ role-based permission แทน level
        // บัญชีทั่วไปดูได้เฉพาะที่ตัวเองสร้าง
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
            'payment_cycle' => 'nullable|string|max:100', // Added payment_cycle
            'pay_to' => 'required|string|max:255',
            'pay_to_address' => 'nullable|string',
            'pay_to_account_no' => 'nullable|string|max:50',
            'pay_to_bank_name' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_amount' => 'nullable|numeric|min:0', // เพิ่ม validate สำหรับ manual amount
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

            // คำนวณยอดเงินรวม (Server-side calculation for security)
            $totalAmount = 0;
            foreach ($request->items as $item) {
                if (!empty($item['description']) && $item['amount'] > 0) {
                    $totalAmount += $item['amount'];
                }
            }

            // คำนวณภาษีหัก ณ ที่จ่าย
            $taxRate = $request->withholding_tax_rate ?? 0.00;

            // กรณีระบุ manual (taxRate = 0) และมี tax amount ส่งมา ให้ใช้ค่าที่ส่งมา
            // กรณี auto (taxRate > 0) ให้คำนวณใหม่เพื่อความปลอดภัย หรือจะใช้ค่าที่ส่งมาก็ได้ (แต่คำนวณใหม่ปลอดภัยกว่า)
            if ($taxRate > 0) {
                $taxAmount = $totalAmount * ($taxRate / 100);
            } else {
                $taxAmount = $request->input('withholding_tax_amount', 0);

                // Validate manual tax amount to not exceed total amount
                if ($taxAmount > $totalAmount) {
                    $taxAmount = $totalAmount;
                }
            }

            $netAmount = $totalAmount - $taxAmount;

            // สร้างใบสำคัญจ่าย
            $voucher = PaymentVoucher::create([
                'voucher_no' => PaymentVoucher::generateVoucherNo($branchCode),
                'branch_code' => $branchCode,
                'voucher_date' => $request->voucher_date,
                'payment_cycle' => $request->payment_cycle, // Added payment_cycle
                'pay_to' => $request->pay_to,
                'pay_to_address' => $request->pay_to_address,
                'pay_to_account_no' => $request->pay_to_account_no,
                'pay_to_bank_name' => $request->pay_to_bank_name,
                'payee_id' => $payee?->id,
                'remark' => $request->remark,
                'status' => $request->submit ? PaymentVoucher::STATUS_PENDING : PaymentVoucher::STATUS_DRAFT,
                'created_by' => Auth::id(),
                'created_by_name' => Auth::user()->name ?? 'System',
                'withholding_tax_rate' => $taxRate,
                'withholding_tax_amount' => $taxAmount,
                'net_amount' => $netAmount,
                'total_amount' => $totalAmount, // Will be updated correctly implicitly or we can set it here
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

                // Notify via LINE
                $lineService = new \App\Services\LineMessagingService();
                $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_PENDING, $request->boolean('is_test'));
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
            'payment_cycle' => 'nullable|string|max:100', // Added payment_cycle
            'pay_to' => 'required|string|max:255',
            'pay_to_address' => 'nullable|string',
            'pay_to_account_no' => 'nullable|string|max:50',
            'pay_to_bank_name' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
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

            // คำนวณยอดเงินรวมและภาษีหัก ณ ที่จ่าย (Server-side calculation)
            $totalAmount = 0;
            // ต้อง loop items จาก request ไม่ใช่ $voucher->items เพราะ items จะถูกลบสร้างใหม่
            // แต่ใน logic เดิมมีการวนลูปข้างล่างเพื่อ create items ใหม่
            // เราจะคำนวณ totalAmount จาก loop ของ request items ก่อนเพื่อคำนวณภาษี
            if ($request->items) {
                foreach ($request->items as $item) {
                    if (!empty($item['description']) && ($item['amount'] ?? 0) > 0) {
                        $totalAmount += $item['amount'];
                    }
                }
            }

            // คำนวณภาษีหัก ณ ที่จ่าย
            $taxRate = $request->withholding_tax_rate ?? 0.00;

            // กรณีระบุ manual (taxRate = 0) และมี tax amount ส่งมา ให้ใช้ค่าที่ส่งมา
            // กรณี auto (taxRate > 0) ให้คำนวณใหม่เพื่อความปลอดภัย
            if ($taxRate > 0) {
                $taxAmount = $totalAmount * ($taxRate / 100);
            } else {
                $taxAmount = $request->input('withholding_tax_amount', 0);

                // Validate manual tax amount to not exceed total amount
                if ($taxAmount > $totalAmount) {
                    $taxAmount = $totalAmount;
                }
            }

            $netAmount = $totalAmount - $taxAmount;

            // อัพเดทข้อมูลหลัก
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'payment_cycle' => $request->payment_cycle, // Added payment_cycle
                'pay_to' => $request->pay_to,
                'pay_to_address' => $request->pay_to_address,
                'pay_to_account_no' => $request->pay_to_account_no,
                'pay_to_bank_name' => $request->pay_to_bank_name,
                'payee_id' => $payee->id ?? $voucher->payee_id,
                'remark' => $request->remark,
                'status' => $request->submit ? PaymentVoucher::STATUS_PENDING : PaymentVoucher::STATUS_DRAFT,
                'withholding_tax_rate' => $taxRate,
                'withholding_tax_amount' => $taxAmount,
                'net_amount' => $netAmount,
                'total_amount' => $totalAmount,
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

            if ($request->submit) {
                PaymentVoucherLog::create([
                    'payment_voucher_id' => $voucher->id,
                    'action' => PaymentVoucherLog::ACTION_SUBMIT,
                    'from_status' => PaymentVoucher::STATUS_DRAFT,
                    'to_status' => PaymentVoucher::STATUS_PENDING,
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? 'System',
                ]);

                // Notify via LINE
                $lineService = new \App\Services\LineMessagingService();
                $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_PENDING, $request->boolean('is_test'));
            }

            DB::commit();

            return redirect()->route('payment-voucher.show', $voucher->id)
                ->with('success', 'แก้ไขใบสำคัญจ่ายเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ลบใบสำคัญจ่าย (Soft Delete)
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
     * ลบใบสำคัญจ่ายและข้อมูลที่เกี่ยวข้องทั้งหมด (Force Delete)
     */


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

        // Notify via LINE
        $lineService = new \App\Services\LineMessagingService();
        $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_REVIEWED, $request->boolean('is_test'));

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

        // Notify via LINE
        $lineService = new \App\Services\LineMessagingService();
        $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_APPROVED, $request->boolean('is_test'));

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

        // Notify via LINE
        $lineService = new \App\Services\LineMessagingService();
        $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_REJECTED, $request->boolean('is_test'));

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'ปฏิเสธใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * ส่งกลับเป็น Draft เพื่อแก้ไข (Reviewer)
     */
    public function rejectToDraft(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:500',
        ]);

        $voucher = PaymentVoucher::findOrFail($id);

        // เฉพาะผู้ที่มีสิทธิ review เท่านั้น
        if (!$voucher->canReview()) {
            return back()->with('error', 'ไม่สามารถส่งกลับแก้ไขได้');
        }

        $oldStatus = $voucher->status;

        $voucher->update([
            'status' => PaymentVoucher::STATUS_DRAFT,
            'review_remark' => $request->remark,
        ]);

        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => 'reject_to_draft',
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_DRAFT,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => $request->remark,
        ]);

        // Notify via LINE (Reject to Draft considers as Reject)
        $lineService = new \App\Services\LineMessagingService();
        $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_REJECTED, $request->boolean('is_test'));

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'ส่งกลับแก้ไขเรียบร้อยแล้ว กรุณาแก้ไขและส่งใหม่อีกครั้ง');
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
            'payer_name' => $request->payer_name,
            'paid_date' => $request->paid_date ?? now(),
        ]);

        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => PaymentVoucherLog::ACTION_COMPLETE,
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_COMPLETED,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => 'ผู้จ่ายเงิน: ' . $request->payer_name,
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'บันทึกสถานะเสร็จสิ้นเรียบร้อยแล้ว');
    }

    /**
     * ยกเลิกสถานะเสร็จสิ้น (กรณีกดผิด) - Revert to Approved
     */
    public function undoComplete(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:500',
        ]);

        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canUndoComplete()) {
            return back()->with('error', 'ไม่สามารถยกเลิกสถานะเสร็จสิ้นได้');
        }

        $oldStatus = $voucher->status;

        // Reset fields related to completion
        $voucher->update([
            'status' => PaymentVoucher::STATUS_APPROVED,
            // 'payer_name' => null, // Optional: Clear payer info? Let's keep it for record or clear it?
            // 'paid_date' => null,  // Clearing might be safer to indicate it's not paid.
        ]);

        // Create Log
        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'action' => 'undo_complete',
            'from_status' => $oldStatus,
            'to_status' => PaymentVoucher::STATUS_APPROVED,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'remark' => $request->remark ?? 'ยกเลิกสถานะเสร็จสิ้น (กดผิด)',
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'ยกเลิกสถานะเสร็จสิ้นเรียบร้อยแล้ว สถานะกลับเป็น "รอจ่ายเงิน"');
    }

    /**
     * รายการรอตรวจสอบ (สำหรับ Approver)
     */
    /**
     * รายการรอตรวจสอบ (สำหรับ Approver) -> จริงๆ คือรายการรออนุมัติ (ที่ Review แล้ว)
     */
    public function pendingList()
    {
        // แก้ไข: หน้า Pending (รออนุมัติ) ต้องดึงข้อมูลที่สถานะเป็น 'reviewed' (ตรวจสอบแล้ว)
        $vouchers = PaymentVoucher::where('status', PaymentVoucher::STATUS_REVIEWED)
            ->orderBy('reviewed_at', 'asc') // เรียงตามวันที่ตรวจสอบล่าสุด
            ->paginate(20);

        return view('payment_voucher.pending', compact('vouchers'));
    }

    /**
     * รายการรอ Review (สำหรับ Reviewer) -> จริงๆ คือรายการรอตรวจสอบ
     */
    public function approvedList()
    {
        // แก้ไข: หน้า Approved (รอตรวจสอบ/Review) ต้องดึงข้อมูลที่สถานะเป็น 'pending' (รอตรวจสอบ)
        $vouchers = PaymentVoucher::where('status', PaymentVoucher::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('payment_voucher.approved', compact('vouchers'));
    }

    /**
     * Export to Excel
     */
    public function exportExcel($id)
    {
        $voucher = PaymentVoucher::with('items')->findOrFail($id);

        return (new PaymentVoucherExport($voucher))->Download();
    }

    /**
     * Print View (HTML)
     */
    public function printPdf($id)
    {
        $voucher = PaymentVoucher::with(['items', 'allAttachments'])->findOrFail($id);

        return view('payment_voucher.print', compact('voucher'));
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

    /**
     * ลบใบสำคัญจ่ายและข้อมูลที่เกี่ยวข้องทั้งหมด (Force Delete)
     */

    public function forceDestroy($id)
    {
        DB::beginTransaction();
        try {
            $voucher = PaymentVoucher::withTrashed()->findOrFail($id);

            // 1. ลบไฟล์แนบทั้งหมดจาก storage
            foreach ($voucher->allAttachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }

            // 2. ลบ folder ของ voucher (ถ้ามี)
            $folderPath = 'payment_vouchers/' . $voucher->id;
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->deleteDirectory($folderPath);
            }

            // 3. ลบ attachments records
            $voucher->allAttachments()->delete();

            // 4. ลบ items ทั้งหมด
            $voucher->items()->delete();

            // 5. ลบ logs ทั้งหมด
            $voucher->logs()->delete();

            // 6. ลบ voucher (force delete จริงๆ)
            $voucher->forceDelete();

            DB::commit();

            return redirect()->route('payment-voucher.index')
                ->with('success', 'ลบใบสำคัญจ่ายและข้อมูลที่เกี่ยวข้องทั้งหมดเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    /**
     * แสดงฟอร์ม Import Excel
     */
    public function showImportForm()
    {
        $branchCodes = PaymentVoucher::$branchCodes;
        return view('payment_voucher.import', compact('branchCodes'));
    }

    /**
     * Import ข้อมูลจาก Excel (แสดง Preview)
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'branch_code' => 'required|string|max:10',
        ]);

        try {
            $file = $request->file('excel_file');
            $filePath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $filePath);

            $importer = new PaymentVoucherImport($fullPath, $request->branch_code);
            $previewResult = $importer->preview();

            // ลบไฟล์ชั่วคราว
            Storage::disk('local')->delete($filePath);

            if ($previewResult['success']) {
                $data = $previewResult['data'];
                $data['branch_code'] = $request->branch_code; // ส่ง branch_code ไปด้วย

                return view('payment_voucher.import_preview', compact('data', 'previewResult'));
            } else {
                return back()
                    ->with('error', 'Import ไม่สำเร็จ: ' . implode(', ', $previewResult['errors']))
                    ->withInput();
            }
        } catch (\Exception $e) {
            return back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * บันทึกข้อมูลจากการ Import
     */
    public function storeImport(Request $request)
    {
        $request->validate([
            'voucher_no' => 'required|string|unique:payment_vouchers,voucher_no',
            'branch_code' => 'required|string',
            'voucher_date' => 'required|date',
            'pay_to' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            // เตรียมข้อมูลสำหรับ importFromPreview
            $data = $request->all(); // รับข้อมูลทั้งหมดจาก Form including items

            // เรียกใช้ Import Class (ใช้ dummy path เพราะไม่ได้อ่านไฟล์แล้ว)
            $importer = new PaymentVoucherImport('', $request->branch_code);
            $result = $importer->importFromPreview($data);

            if ($result['success']) {
                return redirect()->route('payment-voucher.show', $result['voucher_id'])
                    ->with('success', "บันทึกข้อมูลเรียบร้อยแล้ว เลขที่: {$result['voucher_no']}");
            } else {
                return back()
                    ->with('error', 'บันทึกไม่สำเร็จ: ' . implode(', ', $result['errors']))
                    ->withInput();
            }
        } catch (\Exception $e) {
            return back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ดาวน์โหลด Template Excel
     */
    public function downloadTemplate()
    {
        $templatePath = public_path('templates/payment_voucher_template.xlsx');

        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'payment_voucher_template.xlsx');
        }

        // ถ้าไม่มี template ให้ใช้ไฟล์ example
        $examplePath = base_path('docs/example.xlsx');
        if (file_exists($examplePath)) {
            return response()->download($examplePath, 'payment_voucher_template.xlsx');
        }

        return back()->with('error', 'ไม่พบไฟล์ Template');
    }
}
