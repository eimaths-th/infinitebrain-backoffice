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

        // Flags for other data types
        $showSchoolRevenue = in_array('EL', $selectedGroups) || in_array('EiMaths', $selectedGroups);
        $showPayroll = in_array('EL', $selectedGroups) || in_array('IF', $selectedGroups); // Assuming payroll is still centered on these for now

        // 1. Revenue: School Receipts
        $schoolRevenue = SchoolPrice::whereHas('rec_school', function ($query) use ($year, $month) {
                $query->whereYear('submission_date', $year)
                    ->whereMonth('submission_date', $month);
            })
            ->sum('total');

        // 2. Revenue: Receipt Vouchers (Completed)
        $rvRevenueQuery = ReceiptVoucher::where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->whereBetween('voucher_date', [$startDate, $endDate]);

        if (!empty($rvBranchCodes)) {
            $rvRevenueQuery->whereIn('branch_code', $rvBranchCodes);
        }
        $rvRevenue = $rvRevenueQuery->sum('net_amount');

        // 3. Expense: Payment Vouchers (Completed)
        $pvExpenseQuery = PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
            ->where('is_salary', false)
            ->whereBetween('voucher_date', [$startDate, $endDate]);

        if (!empty($pvBranchCodes)) {
            $pvExpenseQuery->whereIn('branch_code', $pvBranchCodes);
        }
        $pvExpense = $pvExpenseQuery->sum('net_amount');

        // 4. Expense: Payroll (Specific Departments)
        $payrollExpense = Payroll::where('status', Payroll::STATUS_PAID)
            ->whereHas('user', function ($query) {
                $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
            })
            ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                $thaiYear = $year + 543;
                $query->where('year', $thaiYear)->where('month', $month);
            })
            ->sum('net_pay');

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
                // 'school_groups' => SchoolPrice::where('course_type', 1)
                //     ->whereHas('rec_school', function($query) use ($year, $month) {
                //         $query->whereYear('submission_date', $year)
                //               ->whereMonth('submission_date', $month);
                //     })
                'school_groups' => SchoolPrice::whereHas('rec_school', function ($query) use ($year, $month) {
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
                    })->sortByDesc('total_amount')->values(),

                'rv_items' => ReceiptVoucher::where('status', ReceiptVoucher::STATUS_COMPLETED)
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->when(!empty($rvBranchCodes), function ($query) use ($rvBranchCodes) {
                        return $query->whereIn('branch_code', $rvBranchCodes);
                    })
                    ->with(['payee', 'receiptItems'])
                    ->get(),

                'pv_items' => PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
                    ->when(!empty($pvBranchCodes), function ($query) use ($pvBranchCodes) {
                        return $query->whereIn('branch_code', $pvBranchCodes);
                    })
                    ->where('is_salary', false)
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->with(['payee', 'expenseItems'])
                    ->get(),

                'pv_groups' => PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
                    ->when(!empty($pvBranchCodes), function ($query) use ($pvBranchCodes) {
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

                'payroll_items' => Payroll::where('status', Payroll::STATUS_PAID)
                    ->whereHas('user', function ($query) {
                        $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
                    })
                    ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                        $thaiYear = $year + 543;
                        $query->where('year', $thaiYear)->where('month', $month);
                    })
                    ->with(['user'])
                    ->get(),

                'payroll_groups' => Payroll::where('status', Payroll::STATUS_PAID)
                    ->whereHas('user', function ($query) {
                        $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
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
                    })->sortByDesc('total_amount')->values(),
            ];
        }

        return $summary;
    }
}
