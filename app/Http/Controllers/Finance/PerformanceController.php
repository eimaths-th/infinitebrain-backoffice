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
        
        // Sum revenue and expenses per month for the given year
        $monthlyPerformance = [];
        
        for ($m = 1; $m <= 12; $m++) {
            $monthlyPerformance[$m] = $this->calculateMonthlyData($year, $m);
        }

        return view('finance.performance.index', compact('monthlyPerformance', 'year'));
    }

    /**
     * Show Monthly Breakdown.
     */
    public function monthly(Request $request, $year, $month)
    {
        $data = $this->calculateMonthlyData($year, $month, true);
        return view('finance.performance.monthly', compact('data', 'year', 'month'));
    }

    /**
     * Calculate all financial data for a specific month.
     */
    private function calculateMonthlyData($year, $month, $getDetails = false)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // 1. Revenue: School Receipts
        // Filter: course_type = 1 and use parent's submission_date
        $schoolRevenue = SchoolPrice::where('course_type', 1)
            ->whereHas('rec_school', function($query) use ($year, $month) {
                $query->whereYear('submission_date', $year)
                      ->whereMonth('submission_date', $month);
            })
            ->sum('total');

        // 2. Revenue: Receipt Vouchers (Completed)
        $rvRevenue = ReceiptVoucher::where('status', ReceiptVoucher::STATUS_COMPLETED)
            ->whereBetween('voucher_date', [$startDate, $endDate])
            ->sum('net_amount');

        // 3. Expense: Payment Vouchers (Completed)
        // Filter: branch_code in ['EL', 'ELPV', 'ELBF']
        $pvExpense = PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
            ->whereIn('branch_code', ['EL', 'ELPV', 'ELBF'])
            ->whereBetween('voucher_date', [$startDate, $endDate])
                ->sum('net_amount');

        // 4. Expense: Payroll (Specific Departments)
        // Note: Payroll is usually linked to a PayrollPeriod, but user mentioned specific depts.
        // We filter by department "วิชาการ" and "สำนักงาน"
        $payrollExpense = Payroll::where('status', Payroll::STATUS_PAID)
            ->whereHas('user', function($query) {
                $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
            })
            ->whereHas('payrollPeriod', function($query) use ($year, $month) {
                $query->where('year', $year)->where('month', $month);
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
            // Add detailed records if needed for the monthly view
            $summary['details'] = [
                'rv_items' => ReceiptVoucher::where('status', ReceiptVoucher::STATUS_COMPLETED)
                    ->whereBetween('voucher_date', [$startDate, $endDate])->get(),
                'pv_items' => PaymentVoucher::where('status', PaymentVoucher::STATUS_APPROVED)
                    ->whereBetween('voucher_date', [$startDate, $endDate])->get(),
                // ... more details can be added
            ];
        }

        return $summary;
    }
}
