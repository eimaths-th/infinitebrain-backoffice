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
     * Print Monthly Breakdown (A4)
     */
    public function printMonthly(Request $request, $year, $month)
    {
        $data = $this->calculateMonthlyData($year, $month, true);
        return view('finance.performance.print', compact('data', 'year', 'month'));
    }

    /**
     * Calculate all financial data for a specific month.
     */
    private function calculateMonthlyData($year, $month, $getDetails = false)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // ... (Revenue and Expense calculation remains same)  ... //
        // 1. Revenue: School Receipts
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
        $pvExpense = PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
            ->whereIn('branch_code', ['EL', 'ELPV', 'ELBF'])
            ->whereBetween('voucher_date', [$startDate, $endDate])
                ->sum('net_amount');

        // 4. Expense: Payroll (Specific Departments)
        $payrollExpense = Payroll::where('status', Payroll::STATUS_PAID)
            ->whereHas('user', function($query) {
                $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
            })
            ->whereHas('payrollPeriod', function($query) use ($year, $month) {
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
                'school_groups' => SchoolPrice::where('course_type', 1)
                    ->whereHas('rec_school', function($query) use ($year, $month) {
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
                    ->with(['payee'])
                    ->get(),
                    
                'pv_items' => PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
                    ->whereIn('branch_code', ['EL', 'ELPV', 'ELBF'])
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->with(['payee'])
                    ->get(),
                    
                'pv_groups' => PaymentVoucher::where('status', PaymentVoucher::STATUS_COMPLETED)
                    ->whereIn('branch_code', ['EL', 'ELPV', 'ELBF'])
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->with(['payee'])
                    ->get()
                    ->groupBy(function($item) {
                        return $item->payee ? $item->payee->name : ($item->pay_to ?? 'อื่นๆ');
                    })
                    ->map(function ($items, $payee) {
                        return (object) [
                            'pay_to' => $payee,
                            'total_amount' => $items->sum('net_amount'),
                        ];
                    })->sortByDesc('total_amount')->values(),
                    
                'payroll_groups' => Payroll::where('status', Payroll::STATUS_PAID)
                    ->whereHas('user', function($query) {
                        $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
                    })
                    ->whereHas('payrollPeriod', function($query) use ($year, $month) {
                        $thaiYear = $year + 543;
                        $query->where('year', $thaiYear)->where('month', $month);
                    })
                    ->with(['user'])
                    ->get()
                    ->groupBy(function($item) {
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
