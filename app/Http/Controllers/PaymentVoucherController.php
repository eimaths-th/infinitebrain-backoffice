<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherItem;
use App\Models\PaymentVoucherLog;
use App\Models\PaymentVoucherAttachment;
use App\Models\Branch;
use App\Models\BranchGroup;
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

        // Filter by branch
        if ($request->filled('branch_code')) {
            $query->where('branch_code', $request->branch_code);
        }

        // Filter by branch group (using new field)
        if ($request->filled('branch_group')) {
            $query->where('group_branch_code', $request->branch_group);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
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
                    ->orWhere('pay_to', 'like', "%$search%")
                    ->orWhere('remark', 'like', "%$search%")
                    ->orWhere('created_by_name', 'like', "%$search%")
                    ->orWhere('payment_cycle', 'like', "%$search%");
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

        // Use role-based permission
        // บัญชีจะเห็นแค่ใบสำคัญจ่ายที่ตัวเองเป็นคนสร้างเท่านั้น
        if ($user->hasRole('account')) {
            $query->where('created_by', Auth::id());
        }

        // External user filter by assigned branches (คัดกรองตามสาขาที่ได้รับมอบหมาย)
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
            if (count($resolvedBranches) > 0) {
                $query->whereIn('branch_code', $resolvedBranches);
            } else {
                $query->where('id', '<', 0); // No branches = no data
            }
        }

        $vouchers = $query->paginate(20);

        return view('payment_voucher.index', compact('vouchers', 'branchCodes', 'groupBranchCodes'));
    }

    /**
     * แสดงฟอร์มสร้างใบสำคัญจ่ายใหม่
     */
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
        $voucherCode = $branch->pv_prefix ?? ($defaultBranch . 'PV');

        $voucherNo = PaymentVoucher::generateVoucherNo($voucherCode);
        return view('payment_voucher.create', compact('voucherNo', 'branchCodes', 'defaultBranch'));
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
            'deductions' => 'nullable|array',
            'deductions.*.description' => 'required_with:deductions|string',
            'deductions.*.amount' => 'required_with:deductions|numeric|min:0',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
            'is_salary' => 'nullable|boolean',
            'category' => 'required_without:is_salary|nullable|string|max:100',
            'voucher_attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
            'items.*.attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $branchCode = $request->branch_code ?? 'EL';

            if (Auth::user()->hasRole('external-branch')) {
                $userBranches = Auth::user()->branches ?? [];
                $resolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
                if (!in_array($branchCode, $resolvedBranches)) {
                    return back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหรือแก้ไขใบสำคัญจ่ายของสาขานี้')->withInput();
                }
            }

            // ค้นหาหรือสร้างข้อมูลผู้รับเงิน (Payee)
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

            // คำนวณยอดรวมรายการหัก
            $totalDeductionAmount = 0;
            if ($request->deductions) {
                foreach ($request->deductions as $deduction) {
                    if (!empty($deduction['description']) && ($deduction['amount'] ?? 0) > 0) {
                        $totalDeductionAmount += $deduction['amount'];
                    }
                }
            }

            // คำนวณภาษีหัก ณ ที่จ่าย
            $taxRate = $request->withholding_tax_rate ?? 0.00;

            if ($taxRate > 0) {
                $taxAmount = $totalAmount * ($taxRate / 100);
            } else {
                $taxAmount = $request->input('withholding_tax_amount', 0);
                if ($taxAmount > $totalAmount) {
                    $taxAmount = $totalAmount;
                }
            }

            // สูตร: ยอดเงินรวม - รายการหัก - หัก ณ ที่จ่าย = ยอดเงินสุทธิ
            $netAmount = $totalAmount - $totalDeductionAmount - $taxAmount;

            // Determine codes from DB
            $branch = Branch::where('code', $request->branch_code)->with('group')->first();
            if (!$branch) {
                return back()->with('error', 'ไม่พบข้อมูลสาขาที่ระบุ')->withInput();
            }

            $isBankFee = $request->category == PaymentVoucher::CAT_BANK_FEE;
            $voucherCode = $isBankFee ? ($branch->bf_prefix ?? $branch->code . 'BF') : ($branch->pv_prefix ?? $branch->code . 'PV');
            $groupBranchCode = $branch->group->code ?? '';

            // กำหนดสถานะ
            $status = $request->submit ? PaymentVoucher::STATUS_PENDING : PaymentVoucher::STATUS_DRAFT;
            $isSkipApproval = false;
            
            if ($request->submit && $request->skip_approval && Auth::user()->can('payment-voucher.approve')) {
                $status = PaymentVoucher::STATUS_APPROVED;
                $isSkipApproval = true;
            }

            $voucher = PaymentVoucher::create([
                'voucher_no' => PaymentVoucher::generateVoucherNo($voucherCode, $request->voucher_date),
                'voucher_code' => $voucherCode,
                'branch_code' => $branchCode,
                'group_branch_code' => $groupBranchCode,
                'voucher_date' => $request->voucher_date,
                'payment_cycle' => $request->payment_cycle,
                'pay_to' => $request->pay_to,
                'pay_to_address' => $request->pay_to_address,
                'pay_to_account_no' => $request->pay_to_account_no,
                'pay_to_bank_name' => $request->pay_to_bank_name,
                'payee_id' => $payee->id ?? null,
                'remark' => $request->remark,
                'status' => $status,
                'created_by' => Auth::id(),
                'created_by_name' => Auth::user()->name ?? 'System',
                'reviewed_by' => $isSkipApproval ? Auth::id() : null,
                'reviewed_by_name' => $isSkipApproval ? (Auth::user()->name ?? 'System') : null,
                'reviewed_at' => $isSkipApproval ? now() : null,
                'approved_by' => $isSkipApproval ? Auth::id() : null,
                'approved_by_name' => $isSkipApproval ? (Auth::user()->name ?? 'System') : null,
                'approved_at' => $isSkipApproval ? now() : null,
                'withholding_tax_rate' => $taxRate,
                'withholding_tax_amount' => $taxAmount,
                'total_deduction_amount' => $totalDeductionAmount,
                'net_amount' => $netAmount,
                'total_amount' => $totalAmount,
                'is_salary' => $request->boolean('is_salary'),
                'category' => $request->boolean('is_salary') ? null : $request->category,
            ]);

            // อัปโหลดไฟล์แนบของ Voucher
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

                    // อัปโหลดไฟล์แนบของ Item
                    if (isset($item['attachments']) && is_array($item['attachments'])) {
                        foreach ($item['attachments'] as $file) {
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $this->storeAttachment($file, $voucher->id, $voucherItem->id, PaymentVoucherAttachment::TYPE_ITEM);
                            }
                        }
                    }
                }
            }

            // สร้างรายการหัก
            $totalDeductionAmount = 0;
            if ($request->deductions) {
                foreach ($request->deductions as $index => $deduction) {
                    if (!empty($deduction['description']) && ($deduction['amount'] ?? 0) > 0) {
                        PaymentVoucherItem::create([
                            'payment_voucher_id' => $voucher->id,
                            'type' => PaymentVoucherItem::TYPE_DEDUCTION,
                            'item_no' => $index + 1,
                            'description' => $deduction['description'],
                            'amount' => $deduction['amount'],
                        ]);
                        $totalDeductionAmount += $deduction['amount'];
                    }
                }
            }

            // อัปเดตยอดรวม (recalculate)
            $netAmount = $totalAmount - $totalDeductionAmount - ($voucher->withholding_tax_amount ?? 0);
            $voucher->update([
                'total_amount' => $totalAmount,
                'total_deduction_amount' => $totalDeductionAmount,
                'net_amount' => $netAmount,
            ]);

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
                if ($isSkipApproval) {
                    // Log Submit
                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_SUBMIT,
                        'from_status' => PaymentVoucher::STATUS_DRAFT,
                        'to_status' => PaymentVoucher::STATUS_PENDING,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                        'remark' => 'ข้ามการอนุมัติ (Skip Approval)',
                    ]);

                    // Log Review
                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_REVIEW,
                        'from_status' => PaymentVoucher::STATUS_PENDING,
                        'to_status' => PaymentVoucher::STATUS_REVIEWED,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                        'remark' => 'ข้ามการอนุมัติ (Skip Approval)',
                    ]);

                    // Log Approve
                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_APPROVE,
                        'from_status' => PaymentVoucher::STATUS_REVIEWED,
                        'to_status' => PaymentVoucher::STATUS_APPROVED,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                        'remark' => 'ข้ามการอนุมัติ (Skip Approval)',
                    ]);

                    // Notify via LINE (Approved)
                    $lineService = new \App\Services\LineMessagingService();
                    $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_APPROVED, $request->boolean('is_test'));
                } else {
                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_SUBMIT,
                        'from_status' => PaymentVoucher::STATUS_DRAFT,
                        'to_status' => PaymentVoucher::STATUS_PENDING,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                    ]);

                    // Notify via LINE (Pending)
                    $lineService = new \App\Services\LineMessagingService();
                    $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_PENDING, $request->boolean('is_test'));
                }
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

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
            if (!in_array($voucher->branch_code, $resolvedBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงใบสำคัญจ่ายของสาขานี้');
            }
        }

        return view('payment_voucher.show', compact('voucher'));
    }

    /**
     * แสดงฟอร์มแก้ไขใบสำคัญจ่าย
     */
    public function edit($id)
    {
        $voucher = PaymentVoucher::with(['items.attachments', 'attachments'])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
            if (!in_array($voucher->branch_code, $resolvedBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงใบสำคัญจ่ายของสาขานี้');
            }
        }

        if (!$voucher->canEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบสำคัญจ่ายนี้ได้แล้ว');
        }

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
        return view('payment_voucher.edit', compact('voucher', 'branchCodes'));
    }

    /**
     * อัปเดตใบสำคัญจ่าย
     */
        public function update(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $resolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
            if (!in_array($voucher->branch_code, $resolvedBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขใบสำคัญจ่ายของสาขานี้');
            }
        }

        if (!$voucher->canEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบสำคัญจ่ายนี้ได้แล้ว');
        }

        $request->validate([
            'voucher_date' => 'required|date',
            'payment_cycle' => 'nullable|string|max:100',
            'pay_to' => 'required|string|max:255',
            'pay_to_address' => 'nullable|string',
            'pay_to_account_no' => 'nullable|string|max:50',
            'pay_to_bank_name' => 'nullable|string|max:100',
            'branch_code' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.description' => 'required_with:deductions|string',
            'deductions.*.amount' => 'required_with:deductions|numeric|min:0',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
            'is_salary' => 'nullable|boolean',
            'category' => 'required_without:is_salary|nullable|string|max:100',
            'voucher_attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
            'items.*.attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $voucher->status;

            // Handle Payee
            $payee = null;
            if ($request->pay_to) {
                $payee = \App\Models\Payee::findOrCreateByName($request->pay_to, [
                    'address' => $request->pay_to_address,
                    'account_no' => $request->pay_to_account_no,
                    'bank_name' => $request->pay_to_bank_name,
                    'created_by' => Auth::id(),
                ]);
            }

            // Calculate Amounts
            $totalAmount = 0;
            foreach ($request->items as $item) {
                if (!empty($item['description']) && $item['amount'] > 0) {
                    $totalAmount += $item['amount'];
                }
            }

            $totalDeductionAmount = 0;
            if ($request->deductions) {
                foreach ($request->deductions as $deduction) {
                    if (!empty($deduction['description']) && ($deduction['amount'] ?? 0) > 0) {
                        $totalDeductionAmount += $deduction['amount'];
                    }
                }
            }

            $taxRate = $request->withholding_tax_rate ?? 0.00;
            if ($taxRate > 0) {
                $taxAmount = $totalAmount * ($taxRate / 100);
            } else {
                $taxAmount = $request->input('withholding_tax_amount', 0);
            }

            $netAmount = $totalAmount - $totalDeductionAmount - $taxAmount;

            $branchCode = $request->branch_code ?? $voucher->branch_code;
            $branch = Branch::where('code', $branchCode)->with('group')->first();
            if (!$branch) {
                return back()->with('error', 'ไม่พบข้อมูลสาขาที่ระบุ')->withInput();
            }

            $isBankFee = $request->category == PaymentVoucher::CAT_BANK_FEE;
            $voucherCode = $isBankFee ? ($branch->bf_prefix ?? $branchCode . 'BF') : ($branch->pv_prefix ?? $branchCode . 'PV');
            $groupBranchCode = $branch->group->code ?? '';

            // Regenerate voucher_no if draft and prefix or voucher_date changed
            if ($voucher->status == PaymentVoucher::STATUS_DRAFT) {
                $expectedPrefixYearMonth = $voucherCode . date('Ym', strtotime($request->voucher_date));
                if (!str_starts_with($voucher->voucher_no, $expectedPrefixYearMonth) || $branchCode != $voucher->branch_code || $voucherCode != $voucher->voucher_code) {
                    $voucher->voucher_no = PaymentVoucher::generateVoucherNo($voucherCode, $request->voucher_date);
                    $voucher->voucher_code = $voucherCode;
                }
            }

            $status = $request->submit ? PaymentVoucher::STATUS_PENDING : $voucher->status;
            $isSkipApproval = false;
            
            if ($request->submit && $request->skip_approval && Auth::user()->can('payment-voucher.approve')) {
                $status = PaymentVoucher::STATUS_APPROVED;
                $isSkipApproval = true;
            }

            // Update Main Record
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'payment_cycle' => $request->payment_cycle,
                'pay_to' => $request->pay_to,
                'pay_to_address' => $request->pay_to_address,
                'pay_to_account_no' => $request->pay_to_account_no,
                'pay_to_bank_name' => $request->pay_to_bank_name,
                'branch_code' => $branchCode,
                'group_branch_code' => $groupBranchCode,
                'voucher_code' => $voucherCode,
                'payee_id' => $payee->id ?? $voucher->payee_id,
                'remark' => $request->remark,
                'status' => $status,
                'reviewed_by' => $isSkipApproval ? Auth::id() : $voucher->reviewed_by,
                'reviewed_by_name' => $isSkipApproval ? (Auth::user()->name ?? 'System') : $voucher->reviewed_by_name,
                'reviewed_at' => $isSkipApproval ? now() : $voucher->reviewed_at,
                'approved_by' => $isSkipApproval ? Auth::id() : $voucher->approved_by,
                'approved_by_name' => $isSkipApproval ? (Auth::user()->name ?? 'System') : $voucher->approved_by_name,
                'approved_at' => $isSkipApproval ? now() : $voucher->approved_at,
                'withholding_tax_rate' => $taxRate,
                'withholding_tax_amount' => $taxAmount,
                'total_deduction_amount' => $totalDeductionAmount,
                'net_amount' => $netAmount,
                'total_amount' => $totalAmount,
                'is_salary' => $request->boolean('is_salary'),
                'category' => $request->boolean('is_salary') ? null : $request->category,
            ]);

            // Handle Attachments
            if ($request->hasFile('voucher_attachments')) {
                foreach ($request->file('voucher_attachments') as $file) {
                    $this->storeAttachment($file, $voucher->id, null, \App\Models\PaymentVoucherAttachment::TYPE_VOUCHER);
                }
            }

            // Update Items (delete and re-create)
            $voucher->items()->delete();

            foreach ($request->items as $index => $item) {
                if (!empty($item['description']) && $item['amount'] > 0) {
                    $voucherItem = PaymentVoucherItem::create([
                        'payment_voucher_id' => $voucher->id,
                        'item_no' => $index + 1,
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                    ]);

                    if (isset($item['attachments']) && is_array($item['attachments'])) {
                        foreach ($item['attachments'] as $file) {
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $this->storeAttachment($file, $voucher->id, $voucherItem->id, \App\Models\PaymentVoucherAttachment::TYPE_ITEM);
                            }
                        }
                    }
                }
            }

            if ($request->deductions) {
                foreach ($request->deductions as $index => $deduction) {
                    if (!empty($deduction['description']) && ($deduction['amount'] ?? 0) > 0) {
                        PaymentVoucherItem::create([
                            'payment_voucher_id' => $voucher->id,
                            'type' => PaymentVoucherItem::TYPE_DEDUCTION,
                            'item_no' => $index + 1,
                            'description' => $deduction['description'],
                            'amount' => $deduction['amount'],
                        ]);
                    }
                }
            }

            // Final Update for amounts
            $voucher->update([
                'total_amount' => $totalAmount,
                'total_deduction_amount' => $totalDeductionAmount,
                'net_amount' => $totalAmount - $totalDeductionAmount - $voucher->withholding_tax_amount,
            ]);

            // Log Update
            PaymentVoucherLog::create([
                'payment_voucher_id' => $voucher->id,
                'action' => PaymentVoucherLog::ACTION_UPDATE,
                'from_status' => $oldStatus,
                'to_status' => $voucher->status,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
            ]);

            if ($request->submit) {
                if ($isSkipApproval) {
                    PaymentVoucherLog::create(['payment_voucher_id' => $voucher->id, 'action' => PaymentVoucherLog::ACTION_SUBMIT, 'from_status' => PaymentVoucher::STATUS_DRAFT, 'to_status' => PaymentVoucher::STATUS_PENDING, 'user_id' => Auth::id(), 'user_name' => Auth::user()->name ?? 'System', 'remark' => 'ข้ามการอนุมัติ (Skip Approval)']);
                    PaymentVoucherLog::create(['payment_voucher_id' => $voucher->id, 'action' => PaymentVoucherLog::ACTION_REVIEW, 'from_status' => PaymentVoucher::STATUS_PENDING, 'to_status' => PaymentVoucher::STATUS_REVIEWED, 'user_id' => Auth::id(), 'user_name' => Auth::user()->name ?? 'System', 'remark' => 'ข้ามการอนุมัติ (Skip Approval)']);
                    PaymentVoucherLog::create(['payment_voucher_id' => $voucher->id, 'action' => PaymentVoucherLog::ACTION_APPROVE, 'from_status' => PaymentVoucher::STATUS_REVIEWED, 'to_status' => PaymentVoucher::STATUS_APPROVED, 'user_id' => Auth::id(), 'user_name' => Auth::user()->name ?? 'System', 'remark' => 'ข้ามการอนุมัติ (Skip Approval)']);

                    $lineService = new \App\Services\LineMessagingService();
                    $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_APPROVED, $request->boolean('is_test'));
                } else {
                    PaymentVoucherLog::create(['payment_voucher_id' => $voucher->id, 'action' => PaymentVoucherLog::ACTION_SUBMIT, 'from_status' => PaymentVoucher::STATUS_DRAFT, 'to_status' => PaymentVoucher::STATUS_PENDING, 'user_id' => Auth::id(), 'user_name' => Auth::user()->name ?? 'System']);
                    $lineService = new \App\Services\LineMessagingService();
                    $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_PENDING, $request->boolean('is_test'));
                }
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
            return back()->with('error', 'ไม่สามารถลบใบสำคัญจ่ายนี้ได้แล้ว');
        }

        $voucher->delete();

        return redirect()->route('payment-voucher.index')
            ->with('success', 'ลบใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * ลบใบสำคัญจ่ายถาวร (Force Delete)
     */


    /**
     * ตรวจสอบสิทธิใบสำคัญจ่าย (Reviewer)
     */
    public function review(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canReview()) {
            return back()->with('error', 'ไม่สามารถตรวจสอบสิทธิใบสำคัญจ่ายนี้ได้แล้ว');
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
            ->with('success', 'ตรวจสอบสิทธิใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * อนุมัติใบสำคัญจ่าย (Approver)
     */
    public function approve(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canApprove()) {
            return back()->with('error', 'ไม่สามารถอนุมัติใบสำคัญจ่ายนี้ได้แล้ว');
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
            return back()->with('error', 'ไม่สามารถปฏิเสธใบสำคัญจ่ายนี้ได้แล้ว');
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
     * ส่งกลับไปเป็น Draft เพื่อแก้ไขใหม่ (Reviewer)
     */
    public function rejectToDraft(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:500',
        ]);

        $voucher = PaymentVoucher::findOrFail($id);

        // ตรวจสอบสิทธิว่าสามารถ review ได้หรือไม่
        if (!$voucher->canReview()) {
            return back()->with('error', 'ไม่สามารถส่งกลับไปแก้ไขใหม่ได้');
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
            ->with('success', 'ส่งกลับไปแก้ไขใหม่เรียบร้อยแล้ว กรุณาดำเนินการแก้ไขและส่งใหม่อีกครั้ง');
    }

    /**
     * เสร็จสิ้นใบสำคัญจ่าย
     */
    public function complete(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canComplete()) {
            return back()->with('error', 'ไม่สามารถทำรายการได้');
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
            'remark' => 'บันทึกการจ่ายเงิน: ' . $request->payer_name,
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'บันทึกสถานะเสร็จสิ้นเรียบร้อยแล้ว');
    }

    /**
     * ดำเนินการ Action แบบทีละหลายรายการ (Bulk Action)
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:review,approve,complete',
            'voucher_ids' => 'required|array|min:1',
            'voucher_ids.*' => 'integer|exists:payment_vouchers,id',
            'remark' => 'nullable|string|max:500',
            // สำหรับ complete
            'payer_name' => 'required_if:action,complete|nullable|string|max:255',
            'paid_date' => 'required_if:action,complete|nullable|date',
            'is_test' => 'nullable|boolean',
        ]);

        $action = $request->action;
        $voucherIds = $request->voucher_ids;
        $remark = $request->remark;
        $payerName = $request->payer_name;
        $paidDate = $request->paid_date;
        $isTest = $request->boolean('is_test');

        $vouchers = PaymentVoucher::whereIn('id', $voucherIds)->get();
        
        $successCount = 0;
        $errorCount = 0;
        
        DB::beginTransaction();
        try {
            $lineService = new \App\Services\LineMessagingService();
            
            foreach ($vouchers as $voucher) {
                // External Branch check
                if (Auth::user()->hasRole('external-branch')) {
                    $userBranches = Auth::user()->branches ?? [];
                    $resolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
                    if (!in_array($voucher->branch_code, $resolvedBranches)) {
                        $errorCount++;
                        continue;
                    }
                }

                $oldStatus = $voucher->status;

                if ($action === 'review') {
                    if (!$voucher->canReview()) {
                        $errorCount++;
                        continue;
                    }
                    
                    $voucher->update([
                        'status' => PaymentVoucher::STATUS_REVIEWED,
                        'reviewed_by' => Auth::id(),
                        'reviewed_by_name' => Auth::user()->name ?? 'System',
                        'reviewed_at' => now(),
                        'review_remark' => $remark,
                    ]);

                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_REVIEW,
                        'from_status' => $oldStatus,
                        'to_status' => PaymentVoucher::STATUS_REVIEWED,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                        'remark' => $remark,
                    ]);
                    
                    $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_REVIEWED, $isTest);
                    $successCount++;
                } 
                elseif ($action === 'approve') {
                    if (!$voucher->canApprove()) {
                        $errorCount++;
                        continue;
                    }
                    
                    $voucher->update([
                        'status' => PaymentVoucher::STATUS_APPROVED,
                        'approved_by' => Auth::id(),
                        'approved_by_name' => Auth::user()->name ?? 'System',
                        'approved_at' => now(),
                        'approve_remark' => $remark,
                    ]);

                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_APPROVE,
                        'from_status' => $oldStatus,
                        'to_status' => PaymentVoucher::STATUS_APPROVED,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                        'remark' => $remark,
                    ]);
                    
                    $lineService->SendPaymentVoucherNotification($voucher, PaymentVoucher::STATUS_APPROVED, $isTest);
                    $successCount++;
                }
                elseif ($action === 'complete') {
                    if (!$voucher->canComplete()) {
                        $errorCount++;
                        continue;
                    }
                    
                    $voucher->update([
                        'status' => PaymentVoucher::STATUS_COMPLETED,
                        'payer_name' => $payerName,
                        'paid_date' => $paidDate ?? now(),
                    ]);

                    PaymentVoucherLog::create([
                        'payment_voucher_id' => $voucher->id,
                        'action' => PaymentVoucherLog::ACTION_COMPLETE,
                        'from_status' => $oldStatus,
                        'to_status' => PaymentVoucher::STATUS_COMPLETED,
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? 'System',
                        'remark' => 'บันทึกการจ่ายเงิน: ' . $payerName,
                    ]);
                    
                    $successCount++;
                }
            }
            DB::commit();
            
            $msg = "ทำรายการสำเร็จ {$successCount} รายการ";
            if ($errorCount > 0) {
                $msg .= ", ไม่สามารถทำรายการได้ {$errorCount} รายการ (เนื่องจากสถานะไม่ถูกต้องหรือไม่มีสิทธิ์)";
            }
            
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ยกเลิกสถานะเสร็จสิ้น (กรณีข้อผิดพลาด) - Revert to Approved
     */
    public function undoComplete(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:500',
        ]);

        $voucher = PaymentVoucher::findOrFail($id);

        if (!$voucher->canUndoComplete()) {
            return back()->with('error', 'ไม่สามารถยกเลิกสถานะเสร็จสิ้นใบสำคัญจ่ายนี้ได้');
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
            'remark' => $request->remark ?? 'ยกเลิกสถานะเสร็จสิ้น (ข้อผิดพลาด)',
        ]);

        return redirect()->route('payment-voucher.show', $voucher->id)
            ->with('success', 'ยกเลิกสถานะเสร็จสิ้นเรียบร้อยแล้ว กลับไปเป็น "อนุมัติแล้ว"');
    }

    /**
     * รายการรออนุมัติ (สำหรับ Approver) -> แสดงรายการที่ Review แล้ว
     */
    public function pendingList()
    {
        // หมายเหตุ: เดิม Pending (รออนุมัติ) แสดงรายการที่ 'reviewed' (ตรวจสอบแล้ว)
        $vouchers = PaymentVoucher::where('status', PaymentVoucher::STATUS_REVIEWED)
            ->orderBy('reviewed_at', 'asc') // เรียงตามเวลาที่ตรวจสอบ เพื่อให้เห็นรายการที่รอนานก่อน
            ->paginate(20);

        return view('payment_voucher.pending', compact('vouchers'));
    }

    /**
     * รายการรอ Review (สำหรับ Reviewer) -> แสดงรายการที่ยังไม่ Review
     */
    public function approvedList()
    {
        // หมายเหตุ: เดิม Approved (อนุมัติ/Review) แสดงรายการที่ 'pending' (รออนุมัติ)
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
     * จัดเก็บไฟล์แนบ
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
            return response()->json(['error' => 'ไม่สามารถลบไฟล์แนบนี้ได้'], 403);
        }

        // ลบไฟล์จาก storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'ลบไฟล์แนบเรียบร้อยแล้ว']);
    }

    /**
     * อัปโหลดไฟล์แนบ (AJAX)
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
            return response()->json(['error' => 'ไม่สามารถอัปโหลดไฟล์แนบได้'], 403);
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
     * สร้างเลขที่ใบสำคัญจ่ายถัดไป
     */
    public function getNextVoucherNo(Request $request)
    {
        $branchCode = $request->get('branch_code', 'EL');
        $category = $request->get('category');

        $branch = Branch::where('code', $branchCode)->first();
        if (!$branch) {
            return response()->json(['error' => 'ไม่พบรหัสสาขา'], 404);
        }

        $isBankFee = $category == PaymentVoucher::CAT_BANK_FEE;
        $voucherCode = $isBankFee ? ($branch->bf_prefix ?? $branchCode . 'BF') : ($branch->pv_prefix ?? $branchCode . 'PV');

        $voucherNo = PaymentVoucher::generateVoucherNo($voucherCode, $request->voucher_date);

        return response()->json([
            'voucher_no' => $voucherNo,
            'branch_code' => $branchCode,
            'voucher_code' => $voucherCode,
        ]);
    }

    /**
     * บันทึกผู้รับเงิน
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
     * ลบใบสำคัญจ่ายทั้งหมด (Force Delete)
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

            // 6. ลบ voucher (force delete ด้วย)
            $voucher->forceDelete();

            DB::commit();

            return redirect()->route('payment-voucher.index')
                ->with('success', 'ลบใบสำคัญจ่ายทั้งหมดเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ไม่สามารถดำเนินการหักเงินได้');
        }
    }
    /**
     * แสดงหน้า Import Excel
     */
    public function showImportForm()
    {
        $branchCodes = Branch::active()->pluck('name', 'code')->toArray();
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
                    ->with('error', 'ไม่สามารถ Import ได้: ' . implode(', ', $previewResult['errors']))
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
            // ใช้ข้อมูลจาก preview เพื่อ importFromPreview
            $data = $request->all(); // ข้อมูลจาก Form including items

            // สร้าง Import Class (ใช้ dummy path เพราะข้อมูลมาจาก Form)
            $importer = new PaymentVoucherImport('', $request->branch_code);
            $result = $importer->importFromPreview($data);

            if ($result['success']) {
                return redirect()->route('payment-voucher.show', $result['voucher_id'])
                    ->with('success', "บันทึกข้อมูลเรียบร้อยแล้ว เลขที่: {$result['voucher_no']}");
            } else {
                return back()
                    ->with('error', 'ไม่สามารถบันทึกข้อมูลได้: ' . implode(', ', $result['errors']))
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

        // ถ้าไม่มี template ให้ใช้ example
        $examplePath = base_path('docs/example.xlsx');
        if (file_exists($examplePath)) {
            return response()->download($examplePath, 'payment_voucher_template.xlsx');
        }

        return back()->with('error', 'ไม่พบไฟล์ Template');
    }

    /**
     * อัปเดตหมวดหมู่ (สำหรับใบสำคัญจ่าย - ไม่ใช่เงินเดือน)
     */
    public function updateCategory(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);
        
        $request->validate([
            'category' => 'nullable|string|max:100',
        ]);

        $oldCategory = $voucher->category;
        $newCategory = $voucher->is_salary ? null : $request->category;

        $voucher->update([
            'category' => $newCategory,
        ]);

        // บันทึก Log
        PaymentVoucherLog::create([
            'payment_voucher_id' => $voucher->id,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'action' => 'update_category',
            'remark' => "เปลี่ยนหมวดหมู่จาก '" . ($voucher->categoryLabels[$oldCategory] ?? '-') . "' เป็น '" . ($voucher->categoryLabels[$newCategory] ?? '-') . "'",
        ]);

        return back()->with('success', 'อัปเดตหมวดหมู่เรียบร้อยแล้ว');
    }
}
