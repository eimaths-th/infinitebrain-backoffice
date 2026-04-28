<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchGroup;
use App\Models\PaymentVoucher;
use App\Models\Payroll;
use App\Models\ReceiptVoucher;
use App\Models\SchoolPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * Dashboard for Financial Performance.
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // allowedGroups should always be codes
        $allowedGroups = BranchGroup::pluck('code')->toArray();

        if ($user->hasRole('external-branch')) {
            $userBranches = $user->branches ?? [];
            $allowedGroups = array_intersect($allowedGroups, $userBranches);
        }

        // Get dynamic mapping code => name
        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        // Default to all allowed groups if none in request
        $selectedGroups = $request->input('groups', $allowedGroups);
        // Ensure selectedGroups only contains valid codes
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);

        if (empty($selectedGroups) && ! empty($allowedGroups)) {
            $selectedGroups = $allowedGroups;
        }

        // selectedBranches logic
        $selectedBranches = $request->input('branches', []);
        // Limit branches to only those belonging to selectedGroups
        $validBranches = Branch::whereHas('group', function ($q) use ($selectedGroups) {
            $q->whereIn('code', $selectedGroups);
        })->pluck('code')->toArray();

        $selectedBranches = array_intersect($selectedBranches, $validBranches);

        // Sum revenue and expenses per month for the given year
        $monthlyPerformance = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyPerformance[$m] = $this->calculateMonthlyData($year, $m, false, $selectedGroups, $selectedBranches);
        }

        // Fetch groups with branches for the UI
        $groupsWithBranches = BranchGroup::with([
            'branches' => function ($q) {
                $q->active();
            },
        ])->whereIn('code', $allowedGroups)->get();

        return view('finance.performance.index', compact('monthlyPerformance', 'year', 'selectedGroups', 'selectedBranches', 'groupMap', 'groupsWithBranches'));
    }

    /**
     * Show Monthly Breakdown.
     */
    public function monthly(Request $request, $year, $month)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowedGroups = BranchGroup::pluck('code')->toArray();
        if ($user->hasRole('external-branch')) {
            $allowedGroups = array_intersect($allowedGroups, $user->branches ?? []);
        }

        $selectedGroups = $request->input('groups', [$allowedGroups[0] ?? 'EL']);
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);

        $selectedBranches = $request->input('branches', []);

        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups, $selectedBranches);

        return view('finance.performance.monthly', compact('data', 'year', 'month', 'selectedGroups', 'selectedBranches', 'groupMap'));
    }

    /**
     * Show Yearly Breakdown.
     */
    public function yearly(Request $request, $year)
    {
        $viewData = $this->getYearlyData($request, $year);

        return view('finance.performance.yearly', $viewData);
    }

    /**
     * พิมพ์รายงานสรุปรายปี (Yearly Summary Print)
     */
    public function printYearly(Request $request, $year)
    {
        $viewData = $this->getYearlyData($request, $year);

        return view('finance.performance.print-yearly', $viewData);
    }

    /**
     * พิมพ์รายงานรายรับอย่างละเอียดรายปี (Yearly Revenue Detail Print)
     */
    public function printYearlyRevenue(Request $request, $year)
    {
        $viewData = $this->getYearlyData($request, $year);

        return view('finance.performance.print-yearly-revenue', $viewData);
    }

    /**
     * พิมพ์รายงานรายจ่ายอย่างละเอียดรายปี (Yearly Expense Detail Print)
     */
    public function printYearlyExpense(Request $request, $year)
    {
        $viewData = $this->getYearlyData($request, $year);

        return view('finance.performance.print-yearly-expense', $viewData);
    }

    /**
     * Helper to get yearly aggregated data
     */
    private function getYearlyData(Request $request, $year, $isEvaluationOnly = false)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowedGroups = BranchGroup::pluck('code')->toArray();
        if ($user->hasRole('external-branch')) {
            $allowedGroups = array_intersect($allowedGroups, $user->branches ?? []);
        }

        $selectedGroups = $request->input('groups', $allowedGroups);
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);
        $selectedBranches = $request->input('branches', []);

        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        // Aggregate data for the whole year
        $yearlyData = [
            'school_revenue' => 0,
            'local_school_revenue' => 0,
            'api_school_revenue' => 0,
            'rv_revenue' => 0,
            'pv_expense' => 0,
            'pv_deductions' => 0,
            'payroll_expense' => 0,
            'payroll_deductions' => 0,
            'total_revenue' => 0,
            'total_expense' => 0,
            'profit' => 0,
            'details' => [
                'school_groups' => collect(),
                'rv_items' => collect(),
                'pv_items' => collect(),
                'pv_groups' => collect(),
                'payroll_items' => collect(),
                'payroll_groups' => collect(),
                'api_revenue_list' => [],
            ],
        ];

        for ($m = 1; $m <= 12; $m++) {
            $data = $this->calculateMonthlyData($year, $m, true, $selectedGroups, $selectedBranches, $isEvaluationOnly);

            $yearlyData['school_revenue'] += $data['school_revenue'];
            $yearlyData['local_school_revenue'] += $data['local_school_revenue'];
            $yearlyData['api_school_revenue'] += $data['api_school_revenue'];
            $yearlyData['rv_revenue'] += $data['rv_revenue'];
            $yearlyData['pv_expense'] += $data['pv_expense'];
            $yearlyData['pv_deductions'] += $data['pv_deductions'];
            $yearlyData['payroll_expense'] += $data['payroll_expense'];
            $yearlyData['payroll_deductions'] += $data['payroll_deductions'];
            $yearlyData['total_revenue'] += $data['total_revenue'];
            $yearlyData['total_expense'] += $data['total_expense'];
            $yearlyData['profit'] += $data['profit'];

            // Merge details
            $yearlyData['details']['school_groups'] = $yearlyData['details']['school_groups']->concat($data['details']['school_groups']);
            $yearlyData['details']['rv_items'] = $yearlyData['details']['rv_items']->concat($data['details']['rv_items']);
            $yearlyData['details']['pv_items'] = $yearlyData['details']['pv_items']->concat($data['details']['pv_items']);
            $yearlyData['details']['pv_groups'] = $yearlyData['details']['pv_groups']->concat($data['details']['pv_groups']);
            $yearlyData['details']['payroll_items'] = $yearlyData['details']['payroll_items']->concat($data['details']['payroll_items']);
            $yearlyData['details']['payroll_groups'] = $yearlyData['details']['payroll_groups']->concat($data['details']['payroll_groups']);
            $yearlyData['details']['api_revenue_list'] = array_merge($yearlyData['details']['api_revenue_list'], $data['details']['api_revenue_list']);
        }

        // Post-process merged details (grouping them again)
        $yearlyData['details']['school_groups'] = $yearlyData['details']['school_groups']->groupBy('group')->map(function ($items, $group) {
            return (object) [
                'group' => $group,
                'title' => $items->first()->title,
                'total_amount' => $items->sum('total_amount'),
                'receipt_count' => $items->sum('receipt_count'),
                'student_count' => $items->sum('student_count'),
            ];
        })->sortByDesc('total_amount')->values();

        $yearlyData['details']['pv_groups'] = $yearlyData['details']['pv_groups']->groupBy('pay_to')->map(function ($items, $payee) {
            return (object) [
                'pay_to' => $payee,
                'total_amount' => $items->sum('total_amount'),
            ];
        })->sortByDesc('total_amount')->values();

        $yearlyData['details']['payroll_groups'] = $yearlyData['details']['payroll_groups']->groupBy('department')->map(function ($items, $dept) {
            return (object) [
                'department' => $dept,
                'total_amount' => $items->sum('total_amount'),
                'employee_count' => $items->max('employee_count'),
            ];
        })->sortByDesc('total_amount')->values();

        // Group API list by branch
        $apiMap = [];
        foreach ($yearlyData['details']['api_revenue_list'] as $item) {
            if (! isset($apiMap[$item->branch_code])) {
                $apiMap[$item->branch_code] = (object) [
                    'branch_code' => $item->branch_code,
                    'branch_name' => $item->branch_name,
                    'revenue' => 0,
                    'source' => $item->source,
                ];
            }
            $apiMap[$item->branch_code]->revenue += $item->revenue;
        }
        $yearlyData['details']['api_revenue_list'] = array_values($apiMap);

        // Fetch groups with branches for the UI filters
        $groupsWithBranches = BranchGroup::with([
            'branches' => function ($q) {
                $q->active();
            },
        ])->whereIn('code', $allowedGroups)->get();

        return [
            'data' => $yearlyData,
            'year' => $year,
            'selectedGroups' => $selectedGroups,
            'selectedBranches' => $selectedBranches,
            'groupMap' => $groupMap,
            'groupsWithBranches' => $groupsWithBranches,
            'allowedGroups' => $allowedGroups,
        ];
    }

    /**
     * Print Monthly Breakdown (A4)
     */
    public function printMonthly(Request $request, $year, $month)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowedGroups = BranchGroup::pluck('code')->toArray();
        if ($user->hasRole('external-branch')) {
            $allowedGroups = array_intersect($allowedGroups, $user->branches ?? []);
        }

        $selectedGroups = $request->input('groups', [$allowedGroups[0] ?? 'EL']);
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);

        $selectedBranches = $request->input('branches', []);

        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups, $selectedBranches);

        return view('finance.performance.print', compact('data', 'year', 'month', 'selectedGroups', 'selectedBranches', 'groupMap'));
    }

    /**
     * พิมพ์รายละเอียดรายรับ (Revenue Details Print - A4)
     * ดึงข้อมูลใบเสร็จทั้งหมดแบ่งตามกลุ่มวิชาสำหรับ School Receipts
     */
    public function printRevenue(Request $request, $year, $month)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowedGroups = BranchGroup::pluck('code')->toArray();
        if ($user->hasRole('external-branch')) {
            $allowedGroups = array_intersect($allowedGroups, $user->branches ?? []);
        }

        $selectedGroups = $request->input('groups', [$allowedGroups[0] ?? 'EL']);
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);

        $selectedBranches = $request->input('branches', []);

        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups, $selectedBranches);

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

        return view('finance.performance.print-revenue', compact('data', 'year', 'month', 'selectedGroups', 'selectedBranches', 'school_receipts_detail', 'groupMap'));
    }

    /**
     * พิมพ์รายละเอียดรายจ่าย (Expense Details Print - A4)
     */
    public function printExpense(Request $request, $year, $month)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowedGroups = BranchGroup::pluck('code')->toArray();
        if ($user->hasRole('external-branch')) {
            $allowedGroups = array_intersect($allowedGroups, $user->branches ?? []);
        }

        $selectedGroups = $request->input('groups', [$allowedGroups[0] ?? 'EL']);
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);

        $selectedBranches = $request->input('branches', []);

        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        $isEvaluation = $request->boolean('evaluation');

        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups, $selectedBranches,$isEvaluation);

        return view('finance.performance.print-expense', compact('data', 'year', 'month', 'selectedGroups', 'selectedBranches', 'groupMap', 'isEvaluation'));
    }

    /**
     * Calculate all financial data for a specific month.
     */
    private function calculateMonthlyData($year, $month, $getDetails = false, $selectedGroups = [], $selectedBranches = [], $isEvaluationOnly = false)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Resolve selected groups (Now using group_branch_code directly)
        $hasSelectedGroups = ! empty($selectedGroups);

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
        $showPayroll = ! empty($payrollDepartments);

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

        if (! empty($selectedBranches)) {
            $rvRevenueQuery->whereIn('branch_code', $selectedBranches);
        } elseif ($hasSelectedGroups) {
            $rvRevenueQuery->whereIn('group_branch_code', $selectedGroups);
        }
        $rvRevenue = $rvRevenueQuery->sum('net_amount');

        $pvExpenseQuery = PaymentVoucher::where('is_salary', false)
            ->whereBetween('voucher_date', [$startDate, $endDate]);

        if ($isEvaluationOnly) {
            $pvExpenseQuery->where('is_evaluation', true);
        } else {
            $pvExpenseQuery->where('status', PaymentVoucher::STATUS_COMPLETED);
        }

        if (! empty($selectedBranches)) {
            $pvExpenseQuery->whereIn('branch_code', $selectedBranches);
        } elseif ($hasSelectedGroups) {
            $pvExpenseQuery->whereIn('group_branch_code', $selectedGroups);
        }
        $pvExpense = $pvExpenseQuery->sum('net_amount');
        $pvDeductions = $pvExpenseQuery->sum(DB::raw('total_deduction_amount + withholding_tax_amount'));

        // 3.1. Revenue from Eimaths API (Math Course Fees)
        $apiSchoolRevenue = 0;
        $apiRevenueData = [];

        // Skip API call if eimaths is not involved in current selection
        $isEiSelected = false;
        if (! empty($selectedBranches)) {
            foreach ($selectedBranches as $bCode) {
                if (str_starts_with($bCode, 'Ei')) {
                    $isEiSelected = true;
                    break;
                }
            }
        } elseif (in_array('EiMaths', $selectedGroups)) {
            $isEiSelected = true;
        }

        if ($isEiSelected) {
            $apiRevenueData = $this->fetchEimathsRevenue($year, $month);
            if (! empty($selectedBranches)) {
                // กรองเอาเฉพาะข้อมูลสาขาที่เลือก และมีอยู่ใน API
                foreach ($selectedBranches as $bCode) {
                    // ตรวจสอบทั้งแบบมี prefix และไม่มี prefix
                    if (isset($apiRevenueData[$bCode])) {
                        $apiSchoolRevenue += (float) $apiRevenueData[$bCode];
                    } else {
                        $strippedCode = str_starts_with($bCode, 'Ei') ? substr($bCode, 2) : $bCode;
                        if (isset($apiRevenueData[$strippedCode])) {
                            $apiSchoolRevenue += (float) $apiRevenueData[$strippedCode];
                        }
                    }
                }
            } elseif ($hasSelectedGroups) {
                // ถ้าเลือกเป็นกลุ่ม ให้ดูว่าสาขาในกลุ่มนั้นมีอะไรบ้างที่อยู่ใน API
                $branchesInGroups = Branch::whereHas('group', function ($q) use ($selectedGroups) {
                    $q->whereIn('code', $selectedGroups);
                })->pluck('code')->toArray();

                foreach ($branchesInGroups as $bCode) {
                    if (isset($apiRevenueData[$bCode])) {
                        $apiSchoolRevenue += (float) $apiRevenueData[$bCode];
                    } else {
                        $strippedCode = str_starts_with($bCode, 'Ei') ? substr($bCode, 2) : $bCode;
                        if (isset($apiRevenueData[$strippedCode])) {
                            $apiSchoolRevenue += (float) $apiRevenueData[$strippedCode];
                        }
                    }
                }
            }
        }

        // 4. Expense: Payroll (ดึงตามแผนกที่กำหนดของแต่ละสาขา)
        $payrollExpense = 0;
        $payrollDeductions = 0;
        if ($showPayroll) {
            $payrollQuery = Payroll::where('status', Payroll::STATUS_PAID)
                ->whereHas('user', function ($query) use ($payrollDepartments) {
                    $query->whereIn('department', $payrollDepartments);
                })
                ->whereHas('payrollPeriod', function ($query) use ($year, $month) {
                    $thaiYear = $year + 543;
                    $query->where('year', $thaiYear)->where('month', $month);
                });

            $payrollExpense = $payrollQuery->sum('net_pay');
            $payrollDeductions = $payrollQuery->sum('total_deduction');
        }

        $totalRevenue = $schoolRevenue + $apiSchoolRevenue + $rvRevenue;
        $totalExpense = $pvExpense + $payrollExpense;
        $profit = $totalRevenue - $totalExpense;

        $summary = [
            'school_revenue' => $schoolRevenue + $apiSchoolRevenue,
            'local_school_revenue' => $schoolRevenue,
            'api_school_revenue' => $apiSchoolRevenue,
            'rv_revenue' => $rvRevenue,
            'pv_expense' => $pvExpense,
            'pv_deductions' => $pvDeductions,
            'payroll_expense' => $payrollExpense,
            'payroll_deductions' => $payrollDeductions,
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
                    ->when(! empty($selectedBranches), function ($query) use ($selectedBranches) {
                        return $query->whereIn('branch_code', $selectedBranches);
                    })
                    ->when(empty($selectedBranches) && $hasSelectedGroups, function ($query) use ($selectedGroups) {
                        return $query->whereIn('group_branch_code', $selectedGroups);
                    })
                    ->with(['payee', 'receiptItems'])
                    ->get(),

                'pv_items' => PaymentVoucher::when(! $isEvaluationOnly, function ($query) {
                    return $query->where('status', PaymentVoucher::STATUS_COMPLETED);
                })
                    ->when(! empty($selectedBranches), function ($query) use ($selectedBranches) {
                        return $query->whereIn('branch_code', $selectedBranches);
                    })
                    ->when(empty($selectedBranches) && $hasSelectedGroups, function ($query) use ($selectedGroups) {
                        return $query->whereIn('group_branch_code', $selectedGroups);
                    })
                    ->when($isEvaluationOnly, function ($query) {
                        return $query->where('is_evaluation', true);
                    })
                    ->where('is_salary', false)
                    ->whereBetween('voucher_date', [$startDate, $endDate])
                    ->with(['payee', 'expenseItems'])
                    ->get(),

                'pv_groups' => PaymentVoucher::when(! $isEvaluationOnly, function ($query) {
                    return $query->where('status', PaymentVoucher::STATUS_COMPLETED);
                })
                    ->when(! empty($selectedBranches), function ($query) use ($selectedBranches) {
                        return $query->whereIn('branch_code', $selectedBranches);
                    })
                    ->when(empty($selectedBranches) && $hasSelectedGroups, function ($query) use ($selectedGroups) {
                        return $query->whereIn('group_branch_code', $selectedGroups);
                    })
                    ->when($isEvaluationOnly, function ($query) {
                        return $query->where('is_evaluation', true);
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

            // Add API Details if any
            $summary['details']['api_revenue_list'] = [];
            if ($apiSchoolRevenue > 0) {
                foreach ($apiRevenueData as $code => $rev) {
                    $rev = (float) $rev;
                    if ($rev <= 0) {
                        continue;
                    }

                    // ตรวจสอบว่าสาขานี้อยู่ในเงื่อนไขการกรองหรือไม่ (รองรับทั้งแบบมี Ei prefix และไม่มี)
                    $isIncluded = false;
                    $localCodes = [$code, 'Ei'.$code]; // สร้างความเป็นไปได้ของโค้ดในระบบเรา

                    if (! empty($selectedBranches)) {
                        foreach ($localCodes as $lc) {
                            if (in_array($lc, $selectedBranches)) {
                                $isIncluded = true;
                                break;
                            }
                        }
                    } elseif ($hasSelectedGroups) {
                        $branchesInGroups = Branch::whereHas('group', function ($q) use ($selectedGroups) {
                            $q->whereIn('code', $selectedGroups);
                        })->pluck('code')->toArray();

                        foreach ($localCodes as $lc) {
                            if (in_array($lc, $branchesInGroups)) {
                                $isIncluded = true;
                                break;
                            }
                        }
                    }

                    if ($isIncluded) {
                        // ค้นหาชื่อสาขาจาก DB โดยลองใช้ทั้งแบบมี prefix และไม่มี
                        $branch = Branch::whereIn('code', $localCodes)->first();
                        $branchName = $branch ? $branch->name : $code;
                        $branchCode = $branch ? $branch->code : $code;

                        $summary['details']['api_revenue_list'][] = (object) [
                            'branch_code' => $branchCode,
                            'branch_name' => $branchName,
                            'revenue' => $rev,
                            'source' => 'Eimaths API (Math Course)',
                        ];
                    }
                }
            }
        }

        return $summary;
    }

    private static $eimathsCache = [];

    private function fetchEimathsRevenue($year, $month)
    {
        $cacheKey = "eimaths_revenue_{$year}_{$month}";
        if (isset(self::$eimathsCache[$cacheKey])) {
            return self::$eimathsCache[$cacheKey];
        }

        $isCurrentMonth = ($year == date('Y') && (int) $month == (int) date('n'));
        $ttl = $isCurrentMonth ? now()->addHour() : now()->addHours(24);

        $data = Cache::remember($cacheKey, $ttl, function () use ($year, $month, $cacheKey) {
            try {
                $response = Http::timeout(5)->get('https://app.eimaths-th.com/api/revenue-summary-by-branch-monthly', [
                    'year' => $year,
                    'month' => $month,
                ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    if (isset($resData['success']) && $resData['success'] && isset($resData['data']['branches'])) {
                        $revenueMap = [];
                        foreach ($resData['data']['branches'] as $branch) {
                            $revenueMap[$branch['branch_code']] = $branch['total_revenue'];
                        }

                        return $revenueMap;
                    }
                }

                Log::warning("Eimaths API failed for {$cacheKey}: ".$response->body());
            } catch (\Exception $e) {
                Log::error("Eimaths API Exception for {$cacheKey}: ".$e->getMessage());
            }

            return [];
        });

        self::$eimathsCache[$cacheKey] = $data;

        return $data;
    }

    /**
     * พิมพ์ใบประเมิน (Evaluation Sheet) รายเดือน
     * ดึงเฉพาะ PaymentVoucher ที่ is_evaluation = true และ status = completed
     */
    public function printEvaluation(Request $request, $year, $month)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowedGroups = BranchGroup::pluck('code')->toArray();
        if ($user->hasRole('external-branch')) {
            $allowedGroups = array_intersect($allowedGroups, $user->branches ?? []);
        }

        $selectedGroups = $request->input('groups', [$allowedGroups[0] ?? 'EL']);
        $selectedGroups = array_intersect($selectedGroups, $allowedGroups);
        $selectedBranches = $request->input('branches', []);
        $groupMap = BranchGroup::whereIn('code', $allowedGroups)->pluck('name', 'code')->toArray();

        // ดึง branch codes ที่อยู่ในกลุ่มที่เลือก
        $branchCodes = ! empty($selectedBranches)
            ? $selectedBranches
            : PaymentVoucher::resolveBranchCodes($selectedGroups);

        $data = $this->calculateMonthlyData($year, $month, true, $selectedGroups, $selectedBranches, true);
        $isEvaluation = true;

        return view('finance.performance.print', compact(
            'data',
            'year',
            'month',
            'selectedGroups',
            'selectedBranches',
            'groupMap',
            'isEvaluation'
        ));
    }

    /**
     * พิมพ์ใบประเมิน (Evaluation Sheet) รายปี
     * ดึงเฉพาะ PaymentVoucher ที่ is_evaluation = true และ status = completed ทั้งปี
     */
    public function printYearlyEvaluation(Request $request, $year)
    {
        $viewData = $this->getYearlyData($request, $year, true);
        $viewData['isEvaluation'] = true;

        return view('finance.performance.print-yearly', $viewData);
    }
}
