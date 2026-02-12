@extends('layouts.main_template')

@section('style')
    <style>
        /* ── Dashboard Cards ── */
        .dashboard-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .dashboard-card .card-body {
            padding: 1.25rem;
        }

        .dashboard-card .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .dashboard-card .card-value {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .dashboard-card .card-label {
            font-size: 0.82rem;
            color: #6c757d;
            font-weight: 500;
        }

        .dashboard-card .card-sub {
            font-size: 0.75rem;
            color: #adb5bd;
        }

        /* ── Icon Colors ── */
        .icon-primary {
            background: rgba(63, 106, 216, 0.12);
            color: #3f6ad8;
        }

        .icon-warning {
            background: rgba(247, 185, 36, 0.15);
            color: #f7b924;
        }

        .icon-success {
            background: rgba(61, 189, 88, 0.12);
            color: #3ac47d;
        }

        .icon-info {
            background: rgba(48, 177, 255, 0.12);
            color: #30b1ff;
        }

        /* ── Section Header ── */
        .section-header {
            font-size: 1.05rem;
            font-weight: 600;
            color: #495057;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }

        .section-header i {
            margin-right: 0.4rem;
            color: #3f6ad8;
        }

        /* ── Chart Wrapper ── */
        .chart-wrapper {
            position: relative;
            height: 280px;
        }

        /* ── Status Pills ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .status-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        /* ── Progress Section ── */
        .payroll-progress .progress {
            height: 12px;
            border-radius: 6px;
        }

        /* ── Table ── */
        .table-dashboard th {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-top: none;
        }

        .table-dashboard td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        /* ── Filter Card ── */
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
        }

        .filter-card .form-select,
        .filter-card .btn {
            border-radius: 8px;
        }

        .filter-card label {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
@endsection

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-graph1 icon-gradient bg-mean-fruit"></i>
                </div>
                <div>
                    Dashboard การเงิน
                    <div class="page-title-subheading">ภาพรวมข้อมูลใบสำคัญจ่าย & เงินเดือนพนักงาน</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- FILTER --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('finance-dashboard.index') }}" class="row align-items-end g-3">
                        <div class="col-auto">
                            <label>เดือน</label>
                            <select name="month" class="form-select form-select-sm">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selected_month == $m ? 'selected' : '' }}>
                                        {{ \App\Models\PayrollPeriod::$thaiMonths[$m] ?? $m }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <label>ปี (ค.ศ.)</label>
                            <select name="year" class="form-select form-select-sm">
                                @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $selected_year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-light">
                                <i class="fa fa-filter me-1"></i>กรอง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 1: SUMMARY CARDS --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: ใบสำคัญจ่ายทั้งหมด --}}
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="card-icon icon-primary">
                            <i class="pe-7s-note2"></i>
                        </div>
                        <div class="text-end">
                            <div class="card-value text-primary">{{ number_format($pv_total_count) }}</div>
                            <div class="card-label">ใบสำคัญจ่ายทั้งหมด</div>
                        </div>
                    </div>
                    <div class="card-sub">
                        ยอดรวม ฿{{ number_format($pv_total_amount, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: รอดำเนินการ --}}
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="card-icon icon-warning">
                            <i class="pe-7s-clock"></i>
                        </div>
                        <div class="text-end">
                            <div class="card-value text-warning">{{ number_format($pv_pending_count) }}</div>
                            <div class="card-label">รอดำเนินการ</div>
                        </div>
                    </div>
                    <div class="card-sub">
                        รอตรวจสอบ {{ $pv_status_counts['pending'] ?? 0 }} |
                        รออนุมัติ {{ $pv_status_counts['reviewed'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: เงินเดือนงวดล่าสุด --}}
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="card-icon icon-success">
                            <i class="pe-7s-wallet"></i>
                        </div>
                        <div class="text-end">
                            <div class="card-value text-success">
                                @if ($pr_latest_period)
                                    ฿{{ number_format($pr_latest_period->total_net_pay, 0) }}
                                @else
                                    -
                                @endif
                            </div>
                            <div class="card-label">เงินเดือนงวดล่าสุด</div>
                        </div>
                    </div>
                    <div class="card-sub">
                        @if ($pr_latest_period)
                            {{ $pr_latest_period->period_name }}
                        @else
                            ไม่มีข้อมูลงวด
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: ยอดจ่ายจริง (เดือนปัจจุบัน) --}}
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="card-icon icon-info">
                            <i class="pe-7s-cash"></i>
                        </div>
                        <div class="text-end">
                            <div class="card-value text-info">฿{{ number_format($pv_month_amount, 2) }}</div>
                            <div class="card-label">ยอดจ่ายจริง (เดือน
                                {{ \App\Models\PayrollPeriod::$thaiMonths[$selected_month] ?? $selected_month }})</div>
                        </div>
                    </div>
                    <div class="card-sub">
                        เฉพาะรายการที่สถานะเสร็จสิ้น
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 2: PAYMENT VOUCHER CHARTS --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Doughnut: สถานะใบสำคัญจ่าย --}}
        <div class="col-lg-5">
            <div class="card shadow-sm" style="border-radius:12px; border:none;">
                <div class="card-body">
                    <h6 class="section-header">
                        <i class="pe-7s-graph3"></i>สถานะใบสำคัญจ่าย
                    </h6>
                    <div class="chart-wrapper" style="height:260px;">
                        <canvas id="pvStatusChart"></canvas>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                        @php
                            $status_chart_colors = [
                                'draft' => '#6c757d',
                                'pending' => '#f7b924',
                                'reviewed' => '#30b1ff',
                                'approved' => '#3ac47d',
                                'rejected' => '#d92550',
                                'completed' => '#3f6ad8',
                            ];
                        @endphp
                        @foreach (\App\Models\PaymentVoucher::$statusLabels as $status => $label)
                            <span class="status-pill bg-light">
                                <span class="dot"
                                    style="background: {{ $status_chart_colors[$status] ?? '#ccc' }};"></span>
                                {{ $label }} ({{ $pv_status_counts[$status] ?? 0 }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Bar: ยอดรายเดือน --}}
        <div class="col-lg-7">
            <div class="card shadow-sm" style="border-radius:12px; border:none;">
                <div class="card-body">
                    <h6 class="section-header">
                        <i class="pe-7s-graph1"></i>ยอดใบสำคัญจ่ายรายเดือน (6 เดือนย้อนหลัง)
                    </h6>
                    <div class="chart-wrapper">
                        <canvas id="pvMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 3: BRANCH SUMMARY + RECENT VOUCHERS --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Branch Summary --}}
        <div class="col-lg-5">
            <div class="card shadow-sm" style="border-radius:12px; border:none;">
                <div class="card-body">
                    <h6 class="section-header">
                        <i class="pe-7s-map-marker"></i>ยอดแยกตามสาขา (เฉพาะที่จ่ายแล้ว)
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-dashboard mb-0">
                            <thead>
                                <tr>
                                    <th>สาขา</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end">ยอดรวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pv_branch_summary as $branch)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark me-1">{{ $branch->branch_code }}</span>
                                            {{ $branch->branch_name }}
                                        </td>
                                        <td class="text-center">{{ number_format($branch->count) }}</td>
                                        <td class="text-end fw-bold">฿{{ number_format($branch->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <td colspan="1" class="text-end fw-bold">รวมทั้งหมด</td>
                                    <td></td>
                                    <td class="text-end fw-bold">฿{{ number_format($pv_branch_summary->sum('total'), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Vouchers --}}
        <div class="col-lg-7">
            <div class="card shadow-sm" style="border-radius:12px; border:none;">
                <div class="card-body">
                    <h6 class="section-header">
                        <i class="pe-7s-note2"></i>ใบสำคัญจ่ายล่าสุด
                        <a href="{{ route('payment-voucher.index') }}" class="float-end text-decoration-none"
                            style="font-size:0.8rem;">
                            ดูทั้งหมด <i class="fa fa-arrow-right"></i>
                        </a>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-dashboard table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>เลขที่</th>
                                    <th>วันที่</th>
                                    <th>จ่ายให้</th>
                                    <th class="text-end">ยอด</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pv_recent as $voucher)
                                    <tr>
                                        <td>
                                            <a href="{{ route('payment-voucher.show', $voucher->id) }}"
                                                class="text-decoration-none fw-bold">
                                                {{ $voucher->voucher_no }}
                                            </a>
                                        </td>
                                        <td>{{ optional($voucher->voucher_date)->format('d/m/Y') }}</td>
                                        <td>{{ Str::limit($voucher->pay_to, 25) }}</td>
                                        <td class="text-end">฿{{ number_format($voucher->total_amount, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $voucher->status_color }}">
                                                {{ $voucher->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลใบสำคัญจ่าย</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 4  : PAYROLL CHARTS --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Doughnut: รายได้แยกประเภท --}}
        <div class="col-lg-5">
            <div class="card shadow-sm" style="border-radius:12px; border:none;">
                <div class="card-body">
                    <h6 class="section-header">
                        <i class="pe-7s-graph3"></i>รายได้แยกประเภท
                        @if ($pr_latest_period)
                            <small class="text-muted">({{ $pr_latest_period->period_name }})</small>
                        @endif
                    </h6>
                    @if (count($pr_income_breakdown) > 0)
                        <div class="chart-wrapper" style="height:260px;">
                            <canvas id="prIncomeChart"></canvas>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                            @foreach ($pr_income_breakdown as $type => $amount)
                                <span class="status-pill bg-light">
                                    {{ \App\Models\Payroll::$incomeTypes[$type] ?? $type }}
                                    (฿{{ number_format($amount, 0) }})
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="pe-7s-info fa-3x mb-3 d-block"></i>
                            ไม่มีข้อมูลรายได้
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Line: เงินเดือนรายเดือน --}}
        <div class="col-lg-7">
            <div class="card shadow-sm" style="border-radius:12px; border:none;">
                <div class="card-body">
                    <h6 class="section-header">
                        <i class="pe-7s-graph1"></i>เงินเดือนรายเดือน (6 เดือนย้อนหลัง)
                    </h6>
                    <div class="chart-wrapper">
                        <canvas id="prMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 5: PAYROLL LATEST PERIOD DETAIL --}}
    {{-- ══════════════════════════════════════════ --}}
    @if ($pr_latest_period)
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="card shadow-sm" style="border-radius:12px; border:none;">
                    <div class="card-body">
                        <h6 class="section-header">
                            <i class="pe-7s-cash"></i>งวดล่าสุด: {{ $pr_latest_period->period_name }}
                        </h6>
                        <div class="mb-3">
                            {!! $pr_latest_period->GetStatusBadge() !!}
                            <span class="text-muted ms-2" style="font-size:0.82rem;">
                                {{ optional($pr_latest_period->start_date)->format('d/m/Y') }}
                                — {{ optional($pr_latest_period->end_date)->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Progress: Income → Deduction → Net --}}
                        @php
                            $total_income_val = (float) $pr_latest_period->total_income;
                            $total_deduction_val = (float) $pr_latest_period->total_deduction;
                            $total_net_val = (float) $pr_latest_period->total_net_pay;
                            $income_pct = $total_income_val > 0 ? 100 : 0;
                            $deduction_pct =
                                $total_income_val > 0 ? round(($total_deduction_val / $total_income_val) * 100, 1) : 0;
                            $net_pct = $total_income_val > 0 ? round(($total_net_val / $total_income_val) * 100, 1) : 0;
                        @endphp
                        <div class="payroll-progress mb-3">
                            <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                                <span class="text-success fw-bold">รายได้รวม</span>
                                <span>฿{{ number_format($total_income_val, 2) }}</span>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width:{{ $income_pct }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                                <span class="text-danger fw-bold">หักรวม</span>
                                <span>฿{{ number_format($total_deduction_val, 2) }} ({{ $deduction_pct }}%)</span>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-danger" style="width:{{ $deduction_pct }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                                <span class="text-primary fw-bold">สุทธิ</span>
                                <span>฿{{ number_format($total_net_val, 2) }} ({{ $net_pct }}%)</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width:{{ $net_pct }}%"></div>
                            </div>
                        </div>

                        {{-- Deduction Breakdown --}}
                        @if (count($pr_deduction_breakdown) > 0)
                            <h6 style="font-size:0.85rem; color:#6c757d; margin-top:1rem;">รายการหัก</h6>
                            @foreach ($pr_deduction_breakdown as $type => $amount)
                                <div class="d-flex justify-content-between"
                                    style="font-size:0.82rem; padding:3px 0; border-bottom:1px solid #f1f1f1;">
                                    <span>{{ \App\Models\Payroll::$deductionTypes[$type] ?? $type }}</span>
                                    <span class="text-danger">-฿{{ number_format($amount, 2) }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payroll History Table --}}
            <div class="col-lg-7">
                <div class="card shadow-sm" style="border-radius:12px; border:none;">
                    <div class="card-body">
                        <h6 class="section-header">
                            <i class="pe-7s-date"></i>ประวัติงวดเงินเดือน (10 งวดล่าสุด)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-dashboard table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>งวด</th>
                                        <th class="text-center">พนักงาน</th>
                                        <th class="text-end">รายได้</th>
                                        <th class="text-end">หัก</th>
                                        <th class="text-end">สุทธิ</th>
                                        <th class="text-center">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pr_recent_periods as $period)
                                        <tr>
                                            <td>
                                                <a href="{{ route('payroll.show', $period->id) }}"
                                                    class="text-decoration-none fw-bold">
                                                    {{ $period->period_name }}
                                                </a>
                                            </td>
                                            <td class="text-center">{{ $period->employee_count ?? 0 }}</td>
                                            <td class="text-end text-success">
                                                ฿{{ number_format($period->total_income, 0) }}</td>
                                            <td class="text-end text-danger">
                                                ฿{{ number_format($period->total_deduction, 0) }}</td>
                                            <td class="text-end fw-bold">฿{{ number_format($period->total_net_pay, 0) }}
                                            </td>
                                            <td class="text-center">{!! $period->GetStatusBadge() !!}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">ไม่มีข้อมูลงวดเงินเดือน
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif



@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── สี Chart ──
            const STATUS_COLORS = {
                draft: '#6c757d',
                pending: '#f7b924',
                reviewed: '#30b1ff',
                approved: '#3ac47d',
                rejected: '#d92550',
                completed: '#3f6ad8'
            };
            const INCOME_COLORS = ['#3ac47d', '#f7b924', '#30b1ff', '#d92550', '#764ba2', '#6c757d'];
            const CHART_FONT = {
                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
            };

            // ════════════════════════════════════════════
            // 1) PV Status Doughnut
            // ════════════════════════════════════════════
            const pvStatusData = @json($pv_status_counts);
            const pvLabels = @json(\App\Models\PaymentVoucher::$statusLabels);

            new Chart(document.getElementById('pvStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(pvStatusData).map(k => pvLabels[k] || k),
                    datasets: [{
                        data: Object.values(pvStatusData),
                        backgroundColor: Object.keys(pvStatusData).map(k => STATUS_COLORS[k] ||
                            '#ccc'),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.label}: ${ctx.raw} รายการ`
                            }
                        }
                    }
                }
            });

            // ════════════════════════════════════════════
            // 2) PV Monthly Bar
            // ════════════════════════════════════════════
            const pvMonthly = @json($pv_monthly_data);

            new Chart(document.getElementById('pvMonthlyChart'), {
                type: 'bar',
                data: {
                    labels: pvMonthly.labels,
                    datasets: [{
                        label: 'ยอดเงิน (฿)',
                        data: pvMonthly.amounts,
                        backgroundColor: 'rgba(63,106,216,0.7)',
                        borderColor: '#3f6ad8',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '฿' + v.toLocaleString(),
                                font: CHART_FONT
                            }
                        },
                        x: {
                            ticks: {
                                font: CHART_FONT
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx =>
                                    `฿${ctx.raw.toLocaleString('th-TH', {minimumFractionDigits: 2})}`
                            }
                        }
                    }
                }
            });

            // ════════════════════════════════════════════
            // 3) Payroll Income Doughnut
            // ════════════════════════════════════════════
            const prIncomeData = @json($pr_income_breakdown);
            const incomeLabels = @json(\App\Models\Payroll::$incomeTypes);
            const prIncomeCanvas = document.getElementById('prIncomeChart');

            if (prIncomeCanvas && Object.keys(prIncomeData).length > 0) {
                new Chart(prIncomeCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(prIncomeData).map(k => incomeLabels[k] || k),
                        datasets: [{
                            data: Object.values(prIncomeData),
                            backgroundColor: INCOME_COLORS.slice(0, Object.keys(prIncomeData)
                                .length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx =>
                                        `${ctx.label}: ฿${ctx.raw.toLocaleString('th-TH', {minimumFractionDigits: 2})}`
                                }
                            }
                        }
                    }
                });
            }

            // ════════════════════════════════════════════
            // 4) Payroll Monthly Line
            // ════════════════════════════════════════════
            const prMonthly = @json($pr_monthly_data);

            new Chart(document.getElementById('prMonthlyChart'), {
                type: 'line',
                data: {
                    labels: prMonthly.labels,
                    datasets: [{
                            label: 'รายได้รวม',
                            data: prMonthly.incomes,
                            borderColor: '#3ac47d',
                            backgroundColor: 'rgba(58,196,125,0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2
                        },
                        {
                            label: 'หักรวม',
                            data: prMonthly.deductions,
                            borderColor: '#d92550',
                            backgroundColor: 'rgba(217,37,80,0.05)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2
                        },
                        {
                            label: 'สุทธิ',
                            data: prMonthly.net_pays,
                            borderColor: '#3f6ad8',
                            backgroundColor: 'rgba(63,106,216,0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 2.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '฿' + v.toLocaleString(),
                                font: CHART_FONT
                            }
                        },
                        x: {
                            ticks: {
                                font: CHART_FONT
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: CHART_FONT
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx =>
                                    `${ctx.dataset.label}: ฿${ctx.raw.toLocaleString('th-TH', {minimumFractionDigits: 2})}`
                            }
                        }
                    }
                }
            });

        });
    </script>
@endsection
