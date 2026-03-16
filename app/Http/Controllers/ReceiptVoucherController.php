<?php

namespace App\Http\Controllers;

use App\Models\ReceiptVoucher;
use App\Models\ReceiptVoucherItem;
use App\Models\ReceiptVoucherLog;
use App\Models\ReceiptVoucherAttachment;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceiptVoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:receipt-voucher.view')->only(['index', 'show', 'printPdf']);
        $this->middleware('permission:receipt-voucher.create')->only(['create', 'store']);
        $this->middleware('permission:receipt-voucher.edit')->only(['edit', 'update', 'uploadAttachment', 'deleteAttachment']);
        $this->middleware('permission:receipt-voucher.delete')->only(['destroy']);
        $this->middleware('permission:receipt-voucher.verify')->only(['verify', 'reject']);
        $this->middleware('permission:receipt-voucher.complete')->only(['complete', 'undoComplete']);
    }

    public function index(Request $request)
    {
        $query = ReceiptVoucher::with(['items', 'logs'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%$search%")
                    ->orWhere('receive_from', 'like', "%$search%")
                    ->orWhere('remark', 'like', "%$search%");
            });
        }

        $vouchers = $query->paginate(20);

        return view('receipt_voucher.index', compact('vouchers'));
    }

    public function create()
    {
        $branchCodes = ReceiptVoucher::$branchCodes;
        $voucherNo = ReceiptVoucher::generateVoucherNo();
        return view('receipt_voucher.create', compact('voucherNo', 'branchCodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'voucher_date' => 'required|date',
            'receive_from' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.description' => 'required_with:deductions|string',
            'deductions.*.amount' => 'required_with:deductions|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = collect($request->items)->sum('amount');
            $totalDeductionAmount = collect($request->deductions)->sum('amount');
            $netAmount = $totalAmount - $totalDeductionAmount;

            $voucher = ReceiptVoucher::create([
                'voucher_no' => ReceiptVoucher::generateVoucherNo($request->branch_code ?? 'ELRV'),
                'voucher_date' => $request->voucher_date,
                'branch_code' => $request->branch_code,
                'receive_from' => $request->receive_from,
                'receive_from_address' => $request->receive_from_address,
                'receive_from_account_no' => $request->receive_from_account_no,
                'receive_from_bank_name' => $request->receive_from_bank_name,
                'total_amount' => $totalAmount,
                'total_deduction_amount' => $totalDeductionAmount,
                'net_amount' => $netAmount,
                'remark' => $request->remark,
                'status' => $request->submit ? ReceiptVoucher::STATUS_PENDING : ReceiptVoucher::STATUS_DRAFT,
                'created_by' => Auth::id(),
                'created_by_name' => Auth::user()->name,
            ]);

            // Save Items
            foreach ($request->items as $index => $item) {
                ReceiptVoucherItem::create([
                    'receipt_voucher_id' => $voucher->id,
                    'type' => 'item',
                    'item_no' => $index + 1,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            // Save Deductions
            if ($request->deductions) {
                foreach ($request->deductions as $index => $deduction) {
                    ReceiptVoucherItem::create([
                        'receipt_voucher_id' => $voucher->id,
                        'type' => 'deduction',
                        'item_no' => $index + 1,
                        'description' => $deduction['description'],
                        'amount' => $deduction['amount'],
                    ]);
                }
            }

            // Save Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($file, $voucher->id);
                }
            }

            // Logs
            ReceiptVoucherLog::create([
                'receipt_voucher_id' => $voucher->id,
                'action' => 'create',
                'to_status' => $voucher->status,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
            ]);

            if ($request->submit) {
                $lineService = new LineMessagingService();
                $lineService->SendReceiptVoucherNotification($voucher, ReceiptVoucher::STATUS_PENDING, $request->boolean('is_test'));
            }

            DB::commit();
            return redirect()->route('receipt-voucher.show', $voucher->id)->with('success', 'บันทึกใบสำคัญรับเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $voucher = ReceiptVoucher::with(['items', 'attachments', 'logs'])->findOrFail($id);
        return view('receipt_voucher.show', compact('voucher'));
    }

    public function edit($id)
    {
        $voucher = ReceiptVoucher::with('items')->findOrFail($id);
        if (!$voucher->canEdit() || !Auth::user()->can('receipt-voucher.edit')) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบสำคัญรับนี้ได้ หรือคุณไม่มีสิทธิ์แก้ไข');
        }
        $branchCodes = ReceiptVoucher::$branchCodes;
        return view('receipt_voucher.edit', compact('voucher', 'branchCodes'));
    }

    public function update(Request $request, $id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        if (!$voucher->canEdit() || !Auth::user()->can('receipt-voucher.edit')) {
            return back()->with('error', 'ไม่สามารถแก้ไขได้ หรือคุณไม่มีสิทธิ์');
        }

        $request->validate([
            'voucher_date' => 'required|date',
            'receive_from' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = collect($request->items)->sum('amount');
            $totalDeductionAmount = collect($request->deductions)->sum('amount');
            $netAmount = $totalAmount - $totalDeductionAmount;

            $oldStatus = $voucher->status;
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'branch_code' => $request->branch_code,
                'receive_from' => $request->receive_from,
                'receive_from_address' => $request->receive_from_address,
                'receive_from_account_no' => $request->receive_from_account_no,
                'receive_from_bank_name' => $request->receive_from_bank_name,
                'total_amount' => $totalAmount,
                'total_deduction_amount' => $totalDeductionAmount,
                'net_amount' => $netAmount,
                'remark' => $request->remark,
                'status' => $request->submit ? ReceiptVoucher::STATUS_PENDING : $voucher->status,
            ]);

            $voucher->items()->delete();
            foreach ($request->items as $index => $item) {
                ReceiptVoucherItem::create([
                    'receipt_voucher_id' => $voucher->id,
                    'type' => 'item',
                    'item_no' => $index + 1,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            if ($request->deductions) {
                foreach ($request->deductions as $index => $deduction) {
                    ReceiptVoucherItem::create([
                        'receipt_voucher_id' => $voucher->id,
                        'type' => 'deduction',
                        'item_no' => $index + 1,
                        'description' => $deduction['description'],
                        'amount' => $deduction['amount'],
                    ]);
                }
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($file, $voucher->id);
                }
            }

            ReceiptVoucherLog::create([
                'receipt_voucher_id' => $voucher->id,
                'action' => 'update',
                'from_status' => $oldStatus,
                'to_status' => $voucher->status,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
            ]);

            if ($request->submit && $oldStatus !== ReceiptVoucher::STATUS_PENDING) {
                $lineService = new LineMessagingService();
                $lineService->SendReceiptVoucherNotification($voucher, ReceiptVoucher::STATUS_PENDING, $request->boolean('is_test'));
            }

            DB::commit();
            return redirect()->route('receipt-voucher.show', $voucher->id)->with('success', 'อัปเดตใบสำคัญรับเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function verify(Request $request, $id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        if (!$voucher->canVerify() || !Auth::user()->can('receipt-voucher.verify')) {
            return back()->with('error', 'ไม่สามารถตรวจสอบใบสำคัญรับนี้ได้ หรือคุณไม่มีสิทธิ์');
        }

        $oldStatus = $voucher->status;
        $voucher->update([
            'status' => ReceiptVoucher::STATUS_VERIFIED,
            'verified_by' => Auth::id(),
            'verified_by_name' => Auth::user()->name,
            'verified_at' => now(),
            'verify_remark' => $request->remark,
        ]);

        ReceiptVoucherLog::create([
            'receipt_voucher_id' => $voucher->id,
            'action' => 'verify',
            'from_status' => $oldStatus,
            'to_status' => $voucher->status,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'remark' => $request->remark,
        ]);

        $lineService = new LineMessagingService();
        $lineService->SendReceiptVoucherNotification($voucher, ReceiptVoucher::STATUS_VERIFIED, $request->boolean('is_test'));

        return redirect()->route('receipt-voucher.show', $voucher->id)->with('success', 'ตรวจสอบใบสำคัญรับเรียบร้อยแล้ว');
    }

    public function reject(Request $request, $id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        $oldStatus = $voucher->status;
        
        $voucher->update([
            'status' => ReceiptVoucher::STATUS_REJECTED,
            'verify_remark' => $request->remark,
        ]);

        ReceiptVoucherLog::create([
            'receipt_voucher_id' => $voucher->id,
            'action' => 'reject',
            'from_status' => $oldStatus,
            'to_status' => $voucher->status,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'remark' => $request->remark,
        ]);

        $lineService = new LineMessagingService();
        $lineService->SendReceiptVoucherNotification($voucher, ReceiptVoucher::STATUS_REJECTED, $request->boolean('is_test'));

        return redirect()->route('receipt-voucher.show', $voucher->id)->with('success', 'ปฏิเสธใบสำคัญรับเรียบร้อยแล้ว');
    }

    public function complete(Request $request, $id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        if (!$voucher->canComplete() || !Auth::user()->can('receipt-voucher.complete')) {
            return back()->with('error', 'ไม่สามารถทำเสร็จสิ้นได้ หรือคุณไม่มีสิทธิ์');
        }

        $oldStatus = $voucher->status;
        $voucher->update([
            'status' => ReceiptVoucher::STATUS_COMPLETED,
            'completed_by' => Auth::id(),
            'completed_by_name' => Auth::user()->name,
            'completed_at' => now(),
            'complete_remark' => $request->remark,
            'receiver_name' => $request->receiver_name,
            'received_date' => $request->received_date ?? now(),
        ]);

        ReceiptVoucherLog::create([
            'receipt_voucher_id' => $voucher->id,
            'action' => 'complete',
            'from_status' => $oldStatus,
            'to_status' => $voucher->status,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'remark' => $request->remark,
        ]);

        $lineService = new LineMessagingService();
        $lineService->SendReceiptVoucherNotification($voucher, ReceiptVoucher::STATUS_COMPLETED, $request->boolean('is_test'));

        return redirect()->route('receipt-voucher.show', $voucher->id)->with('success', 'ดำเนินการใบสำคัญรับเสร็จสิ้นเรียบร้อยแล้ว');
    }

    public function undoComplete(Request $request, $id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        if ($voucher->status !== ReceiptVoucher::STATUS_COMPLETED) {
            return back()->with('error', 'สถานะไม่ใช่ Completed');
        }

        $oldStatus = $voucher->status;
        $voucher->update([
            'status' => ReceiptVoucher::STATUS_VERIFIED,
        ]);

        ReceiptVoucherLog::create([
            'receipt_voucher_id' => $voucher->id,
            'action' => 'undo_complete',
            'from_status' => $oldStatus,
            'to_status' => $voucher->status,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'remark' => $request->remark,
        ]);

        return redirect()->route('receipt-voucher.show', $voucher->id)->with('success', 'ยกเลิกสถานะเสร็จสิ้นเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        if (!$voucher->canEdit() || !Auth::user()->can('receipt-voucher.delete')) {
            return back()->with('error', 'ไม่สามารถลบได้ หรือคุณไม่มีสิทธิ์');
        }
        $voucher->delete();
        return redirect()->route('receipt-voucher.index')->with('success', 'ลบใบสำคัญรับเรียบร้อยแล้ว');
    }

    private function storeAttachment($file, $voucherId)
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('receipt_vouchers/' . $voucherId, $fileName, 'public');

        return ReceiptVoucherAttachment::create([
            'receipt_voucher_id' => $voucherId,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'uploaded_by_name' => Auth::user()->name,
        ]);
    }

    public function getNextVoucherNo(Request $request)
    {
        $branchCode = $request->branch_code ?? 'ELRV';
        return response()->json(['voucher_no' => ReceiptVoucher::generateVoucherNo($branchCode)]);
    }

    public function printPdf($id)
    {
        $voucher = ReceiptVoucher::with(['items', 'attachments'])->findOrFail($id);
        return view('receipt_voucher.print', compact('voucher'));
    }

    public function uploadAttachment(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $voucher = ReceiptVoucher::findOrFail($id);
        $this->storeAttachment($request->file('file'), $voucher->id);

        return back()->with('success', 'อัปโหลดไฟล์แนบเรียบร้อยแล้ว');
    }

    public function deleteAttachment($id)
    {
        $attachment = ReceiptVoucherAttachment::findOrFail($id);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'ลบไฟล์แนบเรียบร้อยแล้ว');
    }
}
