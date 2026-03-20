<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PaymentVoucher;
use App\Models\Receipt_School;
use App\Models\ReceiptVoucher;
use App\Models\Payroll;
use App\Models\SchoolPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * Dashboard for Financial Performance.
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $selectedGroups = $request->input('groups', ['EL']); // Default to EL as requested

        // Sum revenue and expenses per month for the given year
        $monthlyPerformance = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyPerformance[$m] = $this->calculateMonthlyData($year, $m, false, $selectedGroups);
        }

        return view('finance.performance.index', compact('monthlyPerformance', 'year', 'selectedGroups'));
    }

    /**
     * Show Monthly Breakdown.
     */
    public function monthly(Request $request, $year, $month)
    {
        $selectedGroups = $request->input('groups', ['EL']);
        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups);
        return view('finance.performance.monthly', compact('data', 'year', 'month', 'selectedGroups'));
    }

    /**
     * Print Monthly Breakdown (A4)
     */
    public function printMonthly(Request $request, $year, $month)
    {
        $selectedGroups = $request->input('groups', ['EL']);
        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups);
        return view('finance.performance.print', compact('data', 'year', 'month', 'selectedGroups'));
    }

    /**
     * พิมพ์รายละเอียดรายรับ (Revenue Details Print - A4)
     * ดึงข้อมูลใบเสร็จทั้งหมดแบ่งตามกลุ่มวิชาสำหรับ School Receipts
     */
    public function printRevenue(Request $request, $year, $month)
    {
        $selectedGroups = $request->input('groups', ['EL']);
        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups);

        // ดึงข้อมูลใบเสร็จ School Receipts แบบละเอียด (เฉพาะสาขา EL เท่านั้น)
        $school_receipts_detail = collect();
        if (in_array('EL', $selectedGroups)) {
            $school_receipts_detail = SchoolPrice::whereHas('rec_school', function ($query) use ($year, $month) {
                    $query->whereYear('submission_date', $year)
                        ->whereMonth('submission_date', $month);
                })
                ->with(['rec_school', 'school_sub', 'std'])
                ->get()
                ->groupBy('group')
                ->map(function ($items, $group) {
                    $model = new SchoolPrice();
                    return (object) [
                        'group_code' => $group,
                        'group_name' => $model->getGroupName($group),
                        'items' => $items->sortBy(function ($item) {
                            return $item->rec_school->submission_date ?? '';
                        })->values(),
                        'total' => $items->sum('total'),
                    ];
                })
                ->sortByDesc('total')
                ->values();
        }

        return view('finance.performance.print-revenue', compact('data', 'year', 'month', 'selectedGroups', 'school_receipts_detail'));
    }

    /**
     * พิมพ์รายละเอียดรายจ่าย (Expense Details Print - A4)
     */
    public function printExpense(Request $request, $year, $month)
    {
        $selectedGroups = $request->input('groups', ['EL']);
        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups);
        return view('finance.performance.print-expense', compact('data', 'year', 'month', 'selectedGroups'));
    }

    /**
     * Calculate all financial data for a specific month.
     */
    private function calculateMonthlyData($year, $month, $getDetails = false, $selectedGroups = [])
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Resolve selected groups to branch codes
        $pvBranchCodes = [];
        $rvBranchCodes = [];
        if (!empty($selectedGroups)) {
            foreach ($selectedGroups as $group) {
                if (isset(PaymentVoucher::$groupBranchCodes[$group])) {
                    $pvBranchCodes = array_merge($pvBranchCodes, PaymentVoucher::$groupBranchCodes[$group]);
                }
                if (isset(ReceiptVoucher::$groupBranchCodes[$group])) {
                    $rvBranchCodes = array_merge($rvBranchCodes, ReceiptVoucher::$groupBranchCodes[$group]);
                }
            }
        }
        $pvBranchCodes = array_unique($pvBranchCodes);
        $rvBranchCodes = array_unique($rvBranchCodes);

        // กำหนดเงื่อนไขตามสาขาที่เลือก
        // School Receipts แสดงเฉพาะสาขา EL เท่านั้น
        $showSchoolRevenue = in_array('EL', $selectedGroups);

        // Payroll แต่ละสาขาดึงแผนกที่แตกต่างกัน
        // EL → วิชาการ, สำนักงาน | IF → คอมพิวเตอร์ | META, EiMaths → ไม่มี Payroll
        $payrollDepartments = [];
        if (in_array('EL', $selectedGroups)) {
            $payrollDepartments = array_merge($payrollDepartments, ['วิชาการ', 'สำนักงาน']);
        }
        if (in_array('IF', $selectedGroups)) {
            $payrollDepartments = array_merge($payrollDepartments, ['คอมพิวเตอร์']);
        }
        // META และ EiMaths ไม่เพิ่มแผนกใดๆ (ไม่มี Payroll)
        $payrollDepartments = array_unique($payrollDepartments);
        $showPayroll = !empty($payrollDepartments);

        // 1. Revenue: School Receipts (เฉพาะสาขา EL เท่านั้น)
        $schoolRevenue = 0;
        if ($showSchoolRevenue) {
            $schoolRevenue = SchoolPrice::whereHas('rec_school', function ($query) use ($year, $month) {
                    $query->whereYear('submission_date', $year)
                        ->whereMonth('submission_date', $month);
                })
                ->sum('total');
        }

        // 2. Revenue: Receipt Vouchers (Completed)
        $rvRevenueQuery = ReceiptVoucher::where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->whereBetween('voucher_date', [$startDate, $endDate]);

        if (!empty($selectedGroups)) {
            $rvRevenueQuery->whereIn('branch_code', $rvBranchCodes);
        }
        $rvRevenue = $rvRevenueQuery->sum('net_amount');

        // 3. Expense: Payment Vouchers (Completed)
        $pvExpenseQuery = PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
            ->where('is_salary', false)
            ->whereBetween('voucher_date', [$startDate, $endDate]);

        if (!empty($selectedGroups)) {
            $pvExpenseQuery->whereIn('branch_code', $pvBranchCodes);
        }
        $pvExpense = $pvExpenseQuery->sum('net_amount');

        // 4. Expense: Payroll (ดึงตามแผนกที่กำหนดของแต่ละสาขา)
        $payrollExpense = 0;
        if ($showPayroll) {
            $payrollExpense = Payroll::where('status', Payroll::STATUS_PAID)
                ->whereHas('user', function ($query) use ($payrollDepartments) {
                    $query->whereIn('department', $payrollDepartments);
                })
                ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                    $thaiYear = $year + 543;
                    $query->where('year', $thaiYear)->where('month', $month);
                })
                ->sum('net_pay');
        }

        $totalRevenue = $schoolRevenue + $rvRevenue;
        $totalExpense = $pvExpense + $payrollExpense;
        $profit = $totalRevenue - $totalExpense;

        $summary = [
            'school_revenue' => $schoolRevenue,
            'rv_revenue' => $rvRevenue,
            'pv_expense' => $pvExpense,
            'payroll_expense' => $payrollExpense,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'profit' => $profit,
        ];

        if ($getDetails) {
            // Detailed Records
            $summary['details'] = [
                // School Receipts (เฉพาะสาขา EL)
                'school_groups' => $showSchoolRevenue
                    ? SchoolPrice::whereHas('rec_school', function ($query) use ($year, $month) {
                            $query->whereYear('submission_date', $year)
                                ->whereMonth('submission_date', $month);
                        })
                        ->with(['school_sub'])
                        ->get()
                        ->groupBy('group')
                        ->map(function ($items, $group) {
                            $model = new SchoolPrice();
                            $groupName = $model->getGroupName($group);
                            return (object) [
                                'group' => $groupName,
                                'title' => $groupName,
                                'total_amount' => $items->sum('total'),
                                'receipt_count' => $items->pluck('rid')->unique()->count(),
                                'student_count' => $items->pluck('student_id')->unique()->count(),
                            ];
                        })->sortByDesc('total_amount')->values()
                    : collect(), // ถ้าไม่ใช่ EL จะส่ง collection ว่าง

                'rv_items' => ReceiptVoucher::where('status', ReceiptVoucher::STATUS_COMPLETED)
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->when(!empty($selectedGroups), function ($query) use ($rvBranchCodes) {
                        return $query->whereIn('branch_code', $rvBranchCodes);
                    })
                    ->with(['payee', 'receiptItems'])
                    ->get(),

                'pv_items' => PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
                    ->when(!empty($selectedGroups), function ($query) use ($pvBranchCodes) {
                        return $query->whereIn('branch_code', $pvBranchCodes);
                    })
                    ->where('is_salary', false)
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->with(['payee', 'expenseItems'])
                    ->get(),

                'pv_groups' => PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
                    ->when(!empty($selectedGroups), function ($query) use ($pvBranchCodes) {
                        return $query->whereIn('branch_code', $pvBranchCodes);
                    })
                    ->where('is_salary', false)
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->with(['payee'])
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->payee ? $item->payee->name : ($item->pay_to ?? 'อื่นๆ');
                    })
                    ->map(function ($items, $payee) {
                        return (object) [
                            'pay_to' => $payee,
                            'total_amount' => $items->sum('net_amount'),
                        ];
                    })->sortByDesc('total_amount')->values(),

                // Payroll Items (ดึงตามแผนกที่กำหนดของสาขาที่เลือก)
                'payroll_items' => $showPayroll
                    ? Payroll::where('status', Payroll::STATUS_PAID)
                        ->whereHas('user', function ($query) use ($payrollDepartments) {
                            $query->whereIn('department', $payrollDepartments);
                        })
                        ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                            $thaiYear = $year + 543;
                            $query->where('year', $thaiYear)->where('month', $month);
                        })
                        ->with(['user'])
                        ->get()
                    : collect(),

                // Payroll Groups (ดึงตามแผนกที่กำหนดของสาขาที่เลือก)
                'payroll_groups' => $showPayroll
                    ? Payroll::where('status', Payroll::STATUS_PAID)
                        ->whereHas('user', function ($query) use ($payrollDepartments) {
                            $query->whereIn('department', $payrollDepartments);
                        })
                        ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                            $thaiYear = $year + 543;
                            $query->where('year', $thaiYear)->where('month', $month);
                        })
                        ->with(['user'])
                        ->get()
                        ->groupBy(function ($item) {
                            return $item->user->department ?? 'Unknown';
                        })
                        ->map(function ($items, $department) {
                            return (object) [
                                'department' => $department,
                                'total_amount' => $items->sum('net_pay'),
                                'employee_count' => $items->count(),
                            ];
                        })->sortByDesc('total_amount')->values()
                    : collect(),
            ];
        }

        return $summary;
    }
}
