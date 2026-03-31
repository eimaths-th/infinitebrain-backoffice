<?php

namespace App\Http\Controllers;

use App\Models\ReceiptVoucher;
use App\Models\ReceiptVoucherItem;
use App\Models\ReceiptVoucherLog;
use App\Models\ReceiptVoucherAttachment;
use App\Models\Branch;
use App\Models\BranchGroup;
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
        $query = ReceiptVoucher::with(['creator'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_code')) {
            $query->where('branch_code', $request->branch_code);
        }

        // Filter by branch group
        if ($request->filled('branch_group')) {
            $query->where('group_branch_code', $request->branch_group);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%$search%")
                    ->orWhere('receive_from', 'like', "%$search%")
                    ->orWhere('remark', 'like', "%$search%");
            });
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Database-driven branch and group lists
        $branchQuery = Branch::active();
        $groupQuery = BranchGroup::has('branches');

        // Filter for external users
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            
            $branchQuery->whereHas('group', function($q) use ($userBranches) {
                $q->whereIn('code', $userBranches);
            });

            $groupQuery->whereIn('code', $userBranches);
        }

        $branchCodes = $branchQuery->pluck('name', 'code')->toArray();
        $groupBranchCodes = $groupQuery->pluck('name', 'code')->toArray();

        if ($user->hasRole('account')) {
            $query->where('created_by', Auth::id());
        }

        // External user filter by assigned branches (รองรับการขยายกลุ่มสาขา)
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches;
            $resolvedBranches = ReceiptVoucher::resolveBranchCodes($userBranches);
            if (count($resolvedBranches) > 0) {
                $query->whereIn('branch_code', $resolvedBranches);
            } else {
                $query->where('id', '<', 0); // No branches = no data
            }
        }

        $vouchers = $query->paginate(20);

        return view('receipt_voucher.index', compact('vouchers', 'branchCodes', 'groupBranchCodes'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $branchQuery = Branch::active();
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $branchQuery->whereHas('group', function($q) use ($userBranches) {
                $q->whereIn('code', $userBranches);
            });
        }
        
        $branchCodes = $branchQuery->pluck('name', 'code')->toArray();
        $defaultBranch = array_key_first($branchCodes) ?? 'EL';

        $branch = Branch::where('code', $defaultBranch)->first();
        $voucherCode = $branch->rv_prefix ?? ($defaultBranch . 'RV');

        $voucherCodeMap = ReceiptVoucher::$voucherCodes[$defaultBranch] ?? null;
        if (is_array($voucherCodeMap)) {
            $voucherCode = $voucherCodeMap['default'] ?? ($defaultBranch . 'RV');
        } else {
            $voucherCode = $voucherCodeMap ?? ($defaultBranch . 'RV');
        }
        
        $voucherNo = ReceiptVoucher::generateVoucherNo($voucherCode);
        return view('receipt_voucher.create', compact('voucherNo', 'branchCodes', 'defaultBranch'));
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
            $branchCode = $request->branch_code ?? 'EL';

            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->hasRole('external-branch')) {
                $userBranches = $user->branches ?? [];
                $resolvedBranches = ReceiptVoucher::resolveBranchCodes($userBranches);
                if (!in_array($branchCode, $resolvedBranches)) {
                    return back()->with('error', 'คุณไม่มีสิทธิ์สร้างและจัดการใบสำคัญรับของสาขานี้')->withInput();
                }
            }

            $branch = Branch::where('code', $branchCode)->with('group')->first();
            if (!$branch) {
                return back()->with('error', 'ไม่พบข้อมูลสาขาที่ระบุ')->withInput();
            }

            $voucherCode = $branch->rv_prefix ?? ($branchCode . 'RV');
            $groupBranchCode = $branch->group->code ?? '';

            $totalAmount = collect($request->items)->sum('amount');
            $totalDeductionAmount = collect($request->deductions)->sum('amount');
            $netAmount = $totalAmount - $totalDeductionAmount;

            // Handle Payee (Remember recipient)
            $payee = \App\Models\Payee::findOrCreateByName($request->receive_from, [
                'address' => $request->receive_from_address,
                'account_no' => $request->receive_from_account_no,
                'bank_name' => $request->receive_from_bank_name,
                'created_by' => Auth::id(),
            ]);

            $voucher = ReceiptVoucher::create([
                'voucher_no' => ReceiptVoucher::generateVoucherNo($voucherCode, $request->voucher_date),
                'voucher_code' => $voucherCode,
                'voucher_date' => $request->voucher_date,
                'branch_code' => $branchCode,
                'group_branch_code' => $groupBranchCode,
                'payee_id' => $payee->id,
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

            // User requested not to send line notification for pending status

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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = ReceiptVoucher::resolveBranchCodes($userBranches);
            if (!in_array($voucher->branch_code, $resolvedBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงใบสำคัญรับของสาขานี้');
            }
        }

        return view('receipt_voucher.show', compact('voucher'));
    }

    public function edit($id)
    {
        $voucher = ReceiptVoucher::with('items')->findOrFail($id);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = ReceiptVoucher::resolveBranchCodes($userBranches);
            if (!in_array($voucher->branch_code, $resolvedBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงใบสำคัญรับของสาขานี้');
            }
        }

        if (!$voucher->canEdit() || !$user->can('receipt-voucher.edit')) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบสำคัญรับนี้ได้ หรือคุณไม่มีสิทธิ์แก้ไข');
        }

        $branchQuery = Branch::active();
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $branchQuery->whereHas('group', function($q) use ($userBranches) {
                $q->whereIn('code', $userBranches);
            });
        }
        
        $branchCodes = $branchQuery->pluck('name', 'code')->toArray();
        return view('receipt_voucher.edit', compact('voucher', 'branchCodes'));
    }

    public function update(Request $request, $id)
    {
        $voucher = ReceiptVoucher::findOrFail($id);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = ReceiptVoucher::resolveBranchCodes($userBranches);
            if (!in_array($voucher->branch_code, $resolvedBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขใบสำคัญรับของสาขานี้');
            }
        }

        if (!$voucher->canEdit() || !$user->can('receipt-voucher.edit')) {
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

            // Handle Payee (Remember recipient)
            $payee = \App\Models\Payee::findOrCreateByName($request->receive_from, [
                'address' => $request->receive_from_address,
                'account_no' => $request->receive_from_account_no,
                'bank_name' => $request->receive_from_bank_name,
                'created_by' => Auth::id(),
            ]);

            $branchCode = $request->branch_code ?? $voucher->branch_code;
            $branch = Branch::where('code', $branchCode)->with('group')->first();
            if (!$branch) {
                return back()->with('error', 'ไม่พบข้อมูลสาขาที่ระบุ')->withInput();
            }

            $voucherCode = $branch->rv_prefix ?? ($branchCode . 'RV');
            $groupBranchCode = $branch->group->code ?? '';

            // If it's a draft and branch/voucher_code or voucher_date changed, regenerate voucher_no
            if ($voucher->status == ReceiptVoucher::STATUS_DRAFT) {
                $expectedPrefixYearMonth = $voucherCode . date('Ym', strtotime($request->voucher_date));
                if (!str_starts_with($voucher->voucher_no, $expectedPrefixYearMonth) || $branchCode != $voucher->branch_code || $voucherCode != $voucher->voucher_code) {
                    $voucher->voucher_no = ReceiptVoucher::generateVoucherNo($voucherCode, $request->voucher_date);
                    $voucher->voucher_code = $voucherCode;
                }
            }

            $oldStatus = $voucher->status;
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'branch_code' => $branchCode,
                'group_branch_code' => $groupBranchCode,
                'voucher_code' => $voucherCode,
                'payee_id' => $payee->id,
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

            // User requested not to send line notification for pending status

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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$voucher->canVerify() || !$user->can('receipt-voucher.verify')) {
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$voucher->canComplete() || !$user->can('receipt-voucher.complete')) {
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$voucher->canEdit() || !$user->can('receipt-voucher.delete')) {
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
        $branchCode = $request->branch_code ?? 'EL';
        $branch = Branch::where('code', $branchCode)->first();
        if (!$branch) {
            return response()->json(['error' => 'ไม่พบรหัสสาขา'], 404);
        }
        $voucherCode = $branch->rv_prefix ?? ($branchCode . 'RV');
        return response()->json(['voucher_no' => ReceiptVoucher::generateVoucherNo($voucherCode, $request->voucher_date)]);
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

    public function searchPayee(Request $request)
    {
        $keyword = $request->get('q', '');
        $payees = \App\Models\Payee::active()
            ->when($keyword, function ($query) use ($keyword) {
                $query->search($keyword);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'address', 'account_no', 'bank_name']);

        return response()->json($payees);
    }

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
     * ลบใบสำคัญรับและข้อมูลที่เกี่ยวข้องทั้งหมด (Force Delete)
     */
    public function forceDestroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('receipt-voucher.delete')) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        DB::beginTransaction();
        try {
            $voucher = ReceiptVoucher::withTrashed()->findOrFail($id);

            // 1. ลบไฟล์แนบทั้งหมดจาก storage
            foreach ($voucher->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }

            // 2. ลบ folder ของ voucher (ถ้ามี)
            $folder_path = 'receipt_vouchers/' . $voucher->id;
            if (Storage::disk('public')->exists($folder_path)) {
                Storage::disk('public')->deleteDirectory($folder_path);
            }

            // 3. ลบ attachments records
            $voucher->attachments()->delete();

            // 4. ลบ items ทั้งหมด
            $voucher->items()->delete();

            // 5. ลบ logs ทั้งหมด
            $voucher->logs()->delete();

            // 6. ลบ voucher (force delete จริงๆ)
            $voucher->forceDelete();

            DB::commit();

            return redirect()->route('receipt-voucher.index')
                ->with('success', 'ลบใบสำคัญรับและข้อมูลที่เกี่ยวข้องทั้งหมดเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
