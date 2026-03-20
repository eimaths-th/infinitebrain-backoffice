<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\PayrollIncomeItem;
use App\Models\PayrollDeductionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    /**
     * Finance Dashboard — สรุปภาพรวม Payment Voucher & Payroll
     */
    public function index(Request $request)
    {
        // ── Filter: เดือน/ปี (default = เดือนปัจจุบัน) ──
        $selected_year  = $request->input('year', now()->year);
        $selected_month = $request->input('month', now()->month);

        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $allowedResolvedBranches = [];
        $allowedGroups = ['EL', 'IF', 'META', 'EiMaths'];
        $isExternal = $user->hasRole('external-branch');

        if ($isExternal) {
            $userBranches = $user->branches ?? [];
            $allowedResolvedBranches = PaymentVoucher::resolveBranchCodes($userBranches);
            $allowedGroups = array_intersect($allowedGroups, $userBranches);
        }

        // ════════════════════════════════════════════════════
        // PAYMENT VOUCHER
        // ════════════════════════════════════════════════════

        // 1) จำนวนแยกตามสถานะ (เฉพาะเดือนที่เลือก)
        $pv_status_counts = PaymentVoucher::select('status', DB::raw('COUNT(*) as count'))
            ->whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pv_total_count = array_sum($pv_status_counts);

        // 2) ยอดรวมทั้งหมด (Global) และ ยอดเดือนที่เลือก
        $pv_total_amount_all = PaymentVoucher::when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })->sum('total_amount');
            
        $pv_month_amount = PaymentVoucher::whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->where('status', PaymentVoucher::STATUS_COMPLETED)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->sum('total_amount');

        // 3) จำนวนรอดำเนินการ (เฉพาะเดือนที่เลือก: pending + reviewed)
        $pv_pending_count = ($pv_status_counts[PaymentVoucher::STATUS_PENDING] ?? 0)
            + ($pv_status_counts[PaymentVoucher::STATUS_REVIEWED] ?? 0);

        // 4) ยอดแยกตามสาขา (เฉพาะที่จ่ายแล้ว)
        // 4.1) ทั้งหมด
        $pv_branch_summary = PaymentVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', PaymentVoucher::STATUS_COMPLETED)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->groupBy('branch_code')
            ->get()
            ->map(function ($item) {
                $item->branch_name = PaymentVoucher::$branchCodes[$item->branch_code] ?? $item->branch_code;
                return $item;
            });

        // 4.2) เฉพาะเดือนที่เลือก
        $pv_branch_monthly_summary = PaymentVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', PaymentVoucher::STATUS_COMPLETED)
            ->whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->groupBy('branch_code')
            ->get()
            ->map(function ($item) {
                $item->branch_name = PaymentVoucher::$branchCodes[$item->branch_code] ?? $item->branch_code;
                return $item;
            });

        // 4.3) เฉพาะปีที่เลือก
        $pv_branch_yearly_summary = PaymentVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', PaymentVoucher::STATUS_COMPLETED)
            ->whereYear('voucher_date', $selected_year)
            ->groupBy('branch_code')
            ->get()
            ->map(function ($item) {
                $item->branch_name = PaymentVoucher::$branchCodes[$item->branch_code] ?? $item->branch_code;
                return $item;
            });

        // 5) รายการล่าสุด 5 รายการ
        $pv_recent = PaymentVoucher::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 6) ยอดรายเดือน 6 เดือนย้อนหลัง (สำหรับ Bar Chart)
        $pv_monthly_data = $this->GetMonthlyVoucherData($selected_year, $selected_month);

        // ════════════════════════════════════════════════════
        // RECEIPT VOUCHER
        // ════════════════════════════════════════════════════

        // 1) จำนวนแยกตามสถานะ (เฉพาะเดือนที่เลือก)
        $rv_status_counts = ReceiptVoucher::select('status', DB::raw('COUNT(*) as count'))
            ->whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $rv_total_count = array_sum($rv_status_counts);

        // 2) ยอดรวมทั้งหมด (Global) และ ยอดเดือนที่เลือก
        $rv_total_amount_all = ReceiptVoucher::when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })->sum('total_amount');

        $rv_month_amount = ReceiptVoucher::whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->sum('total_amount');

        // 3) จำนวนรอดำเนินการ (เฉพาะเดือนที่เลือก: pending + verified)
        $rv_pending_count = ($rv_status_counts[ReceiptVoucher::STATUS_PENDING] ?? 0)
            + ($rv_status_counts[ReceiptVoucher::STATUS_VERIFIED] ?? 0);

        // 4) ยอดแยกตามสาขา (เฉพาะที่รับแล้ว/เสร็จสิ้น)
        // 4.1) ทั้งหมด
        $rv_branch_summary = ReceiptVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->groupBy('branch_code')
            ->get()
            ->map(function ($item) {
                $item->branch_name = ReceiptVoucher::$branchCodes[$item->branch_code] ?? $item->branch_code;
                return $item;
            });

        // 4.2) เฉพาะเดือนที่เลือก
        $rv_branch_monthly_summary = ReceiptVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->when($isExternal, function ($q) use ($allowedResolvedBranches) {
                return $q->whereIn('branch_code', $allowedResolvedBranches);
            })
            ->groupBy('branch_code')
            ->get()
            ->map(function ($item) {
                $item->branch_name = ReceiptVoucher::$branchCodes[$item->branch_code] ?? $item->branch_code;
                return $item;
            });

        // 4.3) เฉพาะปีที่เลือก
        $rv_branch_yearly_summary = ReceiptVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->whereYear('voucher_date', $selected_year)
            ->groupBy('branch_code')
            ->get()
            ->map(function ($item) {
                $item->branch_name = ReceiptVoucher::$branchCodes[$item->branch_code] ?? $item->branch_code;
                return $item;
            });

        // 5) รายการล่าสุด 5 รายการ
        $rv_recent = ReceiptVoucher::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 6) ยอดรายเดือน 6 เดือนย้อนหลัง
        $rv_monthly_data = $this->GetMonthlyReceiptVoucherData($selected_year, $selected_month);

        // ════════════════════════════════════════════════════
        // PAYROLL
        // ════════════════════════════════════════════════════

        // 1) จำนวนงวดแยกสถานะ (เฉพาะปีที่เลือก)
        $pr_year_be = $selected_year + 543;
        $pr_status_counts = PayrollPeriod::select('status', DB::raw('COUNT(*) as count'))
            ->where('year', $pr_year_be)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 2) งวดของเดือนที่เลือก (เฉพาะรายเดือน)
        $pr_selected_period = PayrollPeriod::with('payrolls')
            ->where('frequency', 'monthly')
            ->where('year', $pr_year_be)
            ->where('month', $selected_month)
            ->first();

        // 3) 10 งวดล่าสุด (Global)
        $pr_recent_periods = PayrollPeriod::orderBy('year', 'desc')
            ->where('frequency', 'monthly')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 4) จำนวนพนักงาน (เฉพาะงวดที่เลือก)
        $pr_employee_count = 0;
        $pr_employee_types = [];
        $pr_income_breakdown = [];
        $pr_deduction_breakdown = [];

        if ($pr_selected_period) {
            $pr_employee_count = $pr_selected_period->payrolls()->distinct('user_id')->count('user_id');

            // 5) จำนวนพนักงานแยกประเภท (เฉพาะงวดที่เลือก)
            $pr_employee_types = $pr_selected_period->payrolls()
                ->select('employee_type', DB::raw('COUNT(DISTINCT user_id) as count'))
                ->groupBy('employee_type')
                ->pluck('count', 'employee_type')
                ->toArray();

            // 6) รายได้แยกประเภท (เฉพาะงวดที่เลือก)
            $selected_payroll_ids = $pr_selected_period->payrolls()->pluck('id');

            $pr_income_breakdown = PayrollIncomeItem::whereIn('payroll_id', $selected_payroll_ids)
                ->select('income_type', DB::raw('SUM(amount * quantity) as total'))
                ->groupBy('income_type')
                ->pluck('total', 'income_type')
                ->toArray();

            $pr_deduction_breakdown = PayrollDeductionItem::whereIn('payroll_id', $selected_payroll_ids)
                ->select('deduction_type', DB::raw('SUM(amount) as total'))
                ->groupBy('deduction_type')
                ->pluck('total', 'deduction_type')
                ->toArray();
        }

        // 7) เงินเดือนสุทธิรายเดือน 6 เดือนย้อนหลัง (Line Chart)
        $pr_monthly_data = $this->GetMonthlyPayrollData($selected_year, $selected_month);

        // ════════════════════════════════════════════════════
        // PERFORMANCE (ผลประกอบการ) - Breakdown by group
        // ════════════════════════════════════════════════════
        $perf_all = $this->calculateGroupPerformance($selected_year, $selected_month, 'ALL');
        $perf_el = in_array('EL', $allowedGroups) ? $this->calculateGroupPerformance($selected_year, $selected_month, 'EL') : ['total_revenue'=>0, 'total_expense'=>0, 'school_revenue'=>0, 'rv_revenue'=>0, 'pv_expense'=>0, 'payroll_expense'=>0, 'net_profit'=>0];
        $perf_if = in_array('IF', $allowedGroups) ? $this->calculateGroupPerformance($selected_year, $selected_month, 'IF') : ['total_revenue'=>0, 'total_expense'=>0, 'school_revenue'=>0, 'rv_revenue'=>0, 'pv_expense'=>0, 'payroll_expense'=>0, 'net_profit'=>0];
        $perf_meta = in_array('META', $allowedGroups) ? $this->calculateGroupPerformance($selected_year, $selected_month, 'META') : ['total_revenue'=>0, 'total_expense'=>0, 'school_revenue'=>0, 'rv_revenue'=>0, 'pv_expense'=>0, 'payroll_expense'=>0, 'net_profit'=>0];
        $perf_eimaths = in_array('EiMaths', $allowedGroups) ? $this->calculateGroupPerformance($selected_year, $selected_month, 'EiMaths') : ['total_revenue'=>0, 'total_expense'=>0, 'school_revenue'=>0, 'rv_revenue'=>0, 'pv_expense'=>0, 'payroll_expense'=>0, 'net_profit'=>0];

        // Yearly
        $perf_yearly_el = $this->calculateGroupPerformance($selected_year, null, 'EL');
        $perf_yearly_if = $this->calculateGroupPerformance($selected_year, null, 'IF');
        $perf_yearly_meta = $this->calculateGroupPerformance($selected_year, null, 'META');
        $perf_yearly_eimaths = $this->calculateGroupPerformance($selected_year, null, 'EiMaths');

        $chart_groups_labels = ['Eye Level', 'Infinite Brain', 'META Knowledge', 'EiMaths'];
        
        $chart_monthly_revenue = [
            $perf_el['total_revenue'], $perf_if['total_revenue'], $perf_meta['total_revenue'], $perf_eimaths['total_revenue']
        ];
        $chart_monthly_expense = [
            $perf_el['total_expense'], $perf_if['total_expense'], $perf_meta['total_expense'], $perf_eimaths['total_expense']
        ];

        $chart_yearly_revenue = [
            $perf_yearly_el['total_revenue'], $perf_yearly_if['total_revenue'], $perf_yearly_meta['total_revenue'], $perf_yearly_eimaths['total_revenue']
        ];
        $chart_yearly_expense = [
            $perf_yearly_el['total_expense'], $perf_yearly_if['total_expense'], $perf_yearly_meta['total_expense'], $perf_yearly_eimaths['total_expense']
        ];

        // ════════════════════════════════════════════════════
        // PASS TO VIEW
        // ════════════════════════════════════════════════════

        return view('finance_dashboard.index', compact(
            'selected_year',
            'selected_month',
            // Payment Voucher
            'pv_status_counts',
            'pv_total_count',
            'pv_total_amount_all',
            'pv_month_amount',
            'pv_pending_count',
            'pv_branch_summary',
            'pv_branch_monthly_summary',
            'pv_branch_yearly_summary',
            'pv_recent',
            'pv_monthly_data',
            // Receipt Voucher
            'rv_status_counts',
            'rv_total_count',
            'rv_total_amount_all',
            'rv_month_amount',
            'rv_pending_count',
            'rv_branch_summary',
            'rv_branch_monthly_summary',
            'rv_branch_yearly_summary',
            'rv_recent',
            'rv_monthly_data',
            // Payroll
            'pr_status_counts',
            'pr_selected_period',
            'pr_recent_periods',
            'pr_employee_count',
            'pr_employee_types',
            'pr_income_breakdown',
            'pr_deduction_breakdown',
            'pr_monthly_data',
            // Performance
            'perf_all',
            'perf_el',
            'perf_if',
            'perf_meta',
            'perf_eimaths',
            'chart_groups_labels',
            'chart_monthly_revenue',
            'chart_monthly_expense',
            'chart_yearly_revenue',
            'chart_yearly_expense'
        ));
    }

    /**
     * คำนวณผลประกอบการแยกตามสาขา (EL, IF, META, EiMaths) หรือทั้งหมด (ALL)
     */
    private function calculateGroupPerformance($year, $month, $groupName = 'ALL')
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $pvBranchCodes = [];
        $rvBranchCodes = [];
        $payrollDepartments = [];
        $showSchoolRevenue = false;

        if ($groupName !== 'ALL') {
            if (isset(PaymentVoucher::$groupBranchCodes[$groupName])) {
                $pvBranchCodes = PaymentVoucher::$groupBranchCodes[$groupName];
            }
            if (isset(ReceiptVoucher::$groupBranchCodes[$groupName])) {
                $rvBranchCodes = ReceiptVoucher::$groupBranchCodes[$groupName];
            }

            if ($groupName === 'EL') {
                $showSchoolRevenue = true;
                $payrollDepartments = ['วิชาการ', 'สำนักงาน'];
            } elseif ($groupName === 'IF') {
                $payrollDepartments = ['คอมพิวเตอร์'];
            }
        } else {
            // ALL groups
            $showSchoolRevenue = true;
        }

        // 1. Revenue: School Receipts (Only EL or ALL)
        $schoolRevenue = 0;
        if ($showSchoolRevenue) {
            $schoolRevenue = \App\Models\SchoolPrice::whereHas('rec_school', function ($query) use ($year, $month) {
                $query->whereYear('submission_date', $year)
                    ->whereMonth('submission_date', $month);
            })->sum('total');
        }

        // 2. Revenue: Receipt Vouchers (Completed)
        $rvQuery = \App\Models\ReceiptVoucher::where('status', \App\Models\ReceiptVoucher::STATUS_COMPLETED)
            ->whereBetween('voucher_date', [$startDate, $endDate]);
        
        if ($groupName !== 'ALL' && !empty($rvBranchCodes)) {
            $rvQuery->whereIn('branch_code', $rvBranchCodes);
        } elseif ($groupName !== 'ALL' && empty($rvBranchCodes)) {
            $rvQuery->whereRaw('1 = 0');
        }
        $rvRevenue = $rvQuery->sum('net_amount');

        // 3. Expense: Payment Vouchers (Completed & Not Salary)
        $pvQuery = \App\Models\PaymentVoucher::where('status', \App\Models\PaymentVoucher::STATUS_COMPLETED)
            ->where('is_salary', false)
            ->whereBetween('voucher_date', [$startDate, $endDate]);

        if ($groupName !== 'ALL' && !empty($pvBranchCodes)) {
            $pvQuery->whereIn('branch_code', $pvBranchCodes);
        } elseif ($groupName !== 'ALL' && empty($pvBranchCodes)) {
            $pvQuery->whereRaw('1 = 0');
        }
        $pvExpense = $pvQuery->sum('net_amount');

        // 4. Expense: Payroll (Paid)
        $payrollQuery = \App\Models\Payroll::where('status', \App\Models\Payroll::STATUS_PAID)
            ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                $thaiYear = $year + 543;
                $query->where('year', $thaiYear)->where('month', $month);
            });

        if ($groupName !== 'ALL') {
            if (!empty($payrollDepartments)) {
                $payrollQuery->whereHas('user', function ($query) use ($payrollDepartments) {
                    $query->whereIn('department', $payrollDepartments);
                });
            } else {
                $payrollQuery->whereRaw('1 = 0');
            }
        }
        $payrollExpense = $payrollQuery->sum('net_pay');

        $totalRevenue = $schoolRevenue + $rvRevenue;
        $totalExpense = $pvExpense + $payrollExpense;
        $netProfit = $totalRevenue - $totalExpense;

        return [
            'school_revenue'  => $schoolRevenue,
            'rv_revenue'      => $rvRevenue,
            'pv_expense'      => $pvExpense,
            'payroll_expense' => $payrollExpense,
            'total_revenue'   => $totalRevenue,
            'total_expense'   => $totalExpense,
            'net_profit'      => $netProfit,
        ];
    }

    /**
     * ดึงยอด Payment Voucher รายเดือน 6 เดือนย้อนหลัง
     */
    private function GetMonthlyVoucherData(int $year, int $month): array
    {
        $labels = [];
        $amounts = [];
        $counts = [];

        $start_date = Carbon::create($year, $month, 1)->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $current_date = $start_date->copy()->addMonths($i);
            $labels[] = $current_date->locale('th')->translatedFormat('M Y');

            $query = PaymentVoucher::whereYear('voucher_date', $current_date->year)
                ->whereMonth('voucher_date', $current_date->month);

            $amounts[] = (float) $query->sum('total_amount');
            $counts[] = (int) $query->count();
        }

        return [
            'labels'  => $labels,
            'amounts' => $amounts,
            'counts'  => $counts,
        ];
    }

    /**
     * แสดงรายละเอียดรายการในสาขา (สำหรับ Drill-down)
     */
    public function branchDetail(Request $request)
    {
        $type = $request->query('type'); // 'pv' or 'rv'
        $branch_code = $request->query('branch_code');
        $period = $request->query('period', 'monthly'); // 'monthly', 'yearly', 'all'
        $year = (int) $request->get('year', date('Y'));
        $month = (int) $request->get('month', date('n'));

        $branch_name = '';
        $vouchers = [];

        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $allVoucherBranches = ($type === 'rv') ? ReceiptVoucher::resolveBranchCodes($userBranches) : PaymentVoucher::resolveBranchCodes($userBranches);
            if (!in_array($branch_code, $allVoucherBranches)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลสาขานี้');
            }
        }

        if ($type === 'rv') {
            $branch_name = ReceiptVoucher::$branchCodes[$branch_code] ?? $branch_code;
            $query = ReceiptVoucher::where('branch_code', $branch_code)
                ->where('status', ReceiptVoucher::STATUS_COMPLETED);

            if ($period === 'monthly') {
                $query->whereYear('voucher_date', $year)->whereMonth('voucher_date', $month);
            } elseif ($period === 'yearly') {
                $query->whereYear('voucher_date', $year);
            }

            $vouchers = $query->orderBy('voucher_date', 'desc')->get();
            $title = "รายละเอียดใบสำคัญรับ: $branch_name";
        } else {
            // Default to PV
            $branch_name = PaymentVoucher::$branchCodes[$branch_code] ?? $branch_code;
            $query = PaymentVoucher::where('branch_code', $branch_code)
                ->where('status', PaymentVoucher::STATUS_COMPLETED);

            if ($period === 'monthly') {
                $query->whereYear('voucher_date', $year)->whereMonth('voucher_date', $month);
            } elseif ($period === 'yearly') {
                $query->whereYear('voucher_date', $year);
            }

            $vouchers = $query->orderBy('voucher_date', 'desc')->get();
            $title = "รายละเอียดใบสำคัญจ่าย: $branch_name";
        }

        return view('finance_dashboard.branch_detail', compact(
            'vouchers',
            'title',
            'type',
            'branch_code',
            'branch_name',
            'period',
            'year',
            'month'
        ));
    }

    /**
     * ดึงยอด Payroll รายเดือน 6 เดือนย้อนหลัง
     */
    private function GetMonthlyPayrollData(int $year, int $month): array
    {
        $labels = [];
        $net_pays = [];
        $incomes = [];
        $deductions = [];

        $start_date = Carbon::create($year, $month, 1)->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $current_date = $start_date->copy()->addMonths($i);
            $labels[] = $current_date->locale('th')->translatedFormat('M Y');

            $thai_year = $current_date->year + 543;
            $period = PayrollPeriod::where('year', $thai_year)
                ->where('month', $current_date->month)
                ->where('frequency', 'monthly')
                ->first();

            if ($period) {
                $net_pays[]   = (float) $period->total_net_pay;
                $incomes[]    = (float) $period->total_income;
                $deductions[] = (float) $period->total_deduction;
            } else {
                $net_pays[]   = 0;
                $incomes[]    = 0;
                $deductions[] = 0;
            }
        }

        return [
            'labels'     => $labels,
            'net_pays'   => $net_pays,
            'incomes'    => $incomes,
            'deductions' => $deductions,
        ];
    }

    /**
     * ดึงยอด Receipt Voucher รายเดือน 6 เดือนย้อนหลัง
     */
    private function GetMonthlyReceiptVoucherData(int $year, int $month): array
    {
        $labels = [];
        $amounts = [];
        $counts = [];

        $start_date = Carbon::create($year, $month, 1)->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $current_date = $start_date->copy()->addMonths($i);
            $labels[] = $current_date->locale('th')->translatedFormat('M Y');

            $query = ReceiptVoucher::whereYear('voucher_date', $current_date->year)
                ->whereMonth('voucher_date', $current_date->month);

            $amounts[] = (float) $query->sum('total_amount');
            $counts[] = (int) $query->count();
        }

        return [
            'labels'  => $labels,
            'amounts' => $amounts,
            'counts'  => $counts,
        ];
    }
}
