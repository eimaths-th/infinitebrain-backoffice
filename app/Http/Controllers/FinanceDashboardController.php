<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
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

        // ════════════════════════════════════════════════════
        // PAYMENT VOUCHER
        // ════════════════════════════════════════════════════

        // 1) จำนวนแยกตามสถานะ
        $pv_status_counts = PaymentVoucher::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pv_total_count = array_sum($pv_status_counts);

        // 2) ยอดรวมทั้งหมด + ยอดเดือนที่เลือก
        $pv_total_amount = PaymentVoucher::sum('total_amount');

        $pv_month_amount = PaymentVoucher::whereYear('voucher_date', $selected_year)
            ->whereMonth('voucher_date', $selected_month)
            ->where('status', PaymentVoucher::STATUS_COMPLETED)
            ->sum('total_amount');

        // 3) จำนวนรอดำเนินการ (pending + reviewed)
        $pv_pending_count = ($pv_status_counts[PaymentVoucher::STATUS_PENDING] ?? 0)
            + ($pv_status_counts[PaymentVoucher::STATUS_REVIEWED] ?? 0);

        // 4) ยอดแยกตามสาขา (เฉพาะที่จ่ายแล้ว)
        $pv_branch_summary = PaymentVoucher::select(
            'branch_code',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', PaymentVoucher::STATUS_COMPLETED)
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
        // PAYROLL
        // ════════════════════════════════════════════════════

        // 1) จำนวนงวดแยกสถานะ
        $pr_status_counts = PayrollPeriod::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 2) งวดล่าสุด (เฉพาะรายเดือน)
        $pr_latest_period = PayrollPeriod::with('payrolls')
            ->where('frequency', 'monthly')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        // 3) 10 งวดล่าสุด (เฉพาะรายเดือน)
        $pr_recent_periods = PayrollPeriod::orderBy('year', 'desc')
            ->where('frequency', 'monthly')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 4) จำนวนพนักงานทั้งหมด (unique)
        $pr_employee_count = Payroll::distinct('user_id')->count('user_id');

        // 5) จำนวนพนักงานแยกประเภท
        $pr_employee_types = Payroll::select('employee_type', DB::raw('COUNT(DISTINCT user_id) as count'))
            ->groupBy('employee_type')
            ->pluck('count', 'employee_type')
            ->toArray();

        // 6) รายได้แยกประเภท (งวดล่าสุด) — สำหรับ Doughnut Chart
        $pr_income_breakdown = [];
        $pr_deduction_breakdown = [];

        if ($pr_latest_period) {
            $latest_payroll_ids = $pr_latest_period->payrolls()->pluck('id');

            $pr_income_breakdown = PayrollIncomeItem::whereIn('payroll_id', $latest_payroll_ids)
                ->select('income_type', DB::raw('SUM(amount * quantity) as total'))
                ->groupBy('income_type')
                ->pluck('total', 'income_type')
                ->toArray();

            $pr_deduction_breakdown = PayrollDeductionItem::whereIn('payroll_id', $latest_payroll_ids)
                ->select('deduction_type', DB::raw('SUM(amount) as total'))
                ->groupBy('deduction_type')
                ->pluck('total', 'deduction_type')
                ->toArray();
        }

        // 7) เงินเดือนสุทธิรายเดือน 6 เดือนย้อนหลัง (Line Chart)
        $pr_monthly_data = $this->GetMonthlyPayrollData($selected_year, $selected_month);

        // ════════════════════════════════════════════════════
        // PASS TO VIEW
        // ════════════════════════════════════════════════════

        return view('finance_dashboard.index', compact(
            'selected_year',
            'selected_month',
            // Payment Voucher
            'pv_status_counts',
            'pv_total_count',
            'pv_total_amount',
            'pv_month_amount',
            'pv_pending_count',
            'pv_branch_summary',
            'pv_recent',
            'pv_monthly_data',
            // Payroll
            'pr_status_counts',
            'pr_latest_period',
            'pr_recent_periods',
            'pr_employee_count',
            'pr_employee_types',
            'pr_income_breakdown',
            'pr_deduction_breakdown',
            'pr_monthly_data'
        ));
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
}
