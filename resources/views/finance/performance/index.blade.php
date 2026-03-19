@extends('layouts.main_template')

@section('content')
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-graph1 icon-gradient bg-midnight-bloom"></i>
                </div>
                <div>ผลประกอบการ (Financial Performance)
                    <div class="page-title-subheading">สรุปรายรับ-รายจ่าย ประจำปี {{ $year + 543 }}</div>
                </div>
            </div>
            <div class="page-title-actions">
                <div class="d-inline-block dropdown">
                    <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn-shadow dropdown-toggle btn btn-info">
                        <span class="btn-icon-wrapper pr-2 opacity-7">
                            <i class="fa fa-calendar fa-w-20"></i>
                        </span>
                        ปี {{ $year + 543 }}
                    </button>
                    <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                        @for ($y = date('Y'); $y >= 2024; $y--)
                            <a href="{{ route('performance.index', array_merge(request()->query(), ['year' => $y])) }}" tabindex="0" class="dropdown-item">ปี {{ $y + 543 }}</a>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="main-card card">
                <div class="card-body">
                    <form action="{{ route('performance.index') }}" method="GET" class="row align-items-center">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <div class="col-md-9">
                            <label class="form-label font-weight-bold">กลุ่มสาขาที่ต้องการดูผลประกอบการ:</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach(\App\Models\PaymentVoucher::$groupBranchCodes as $groupName => $codes)
                                    <div class="form-check me-4">
                                        <input class="form-check-input" type="checkbox" name="groups[]" value="{{ $groupName }}" 
                                            id="group_{{ $groupName }}" {{ in_array($groupName, $selectedGroups) ? 'checked' : '' }}>
                                        <label class="form-check-label text-primary font-weight-bold" for="group_{{ $groupName }}">
                                            {{ $groupName }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> กรองข้อมูล
                            </button>
                            <a href="{{ route('performance.index', ['year' => $year, 'groups[]' => 'EL']) }}" class="btn btn-secondary ms-2">
                                รีเซ็ต (EL)
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header">ผลประกอบการรายเดือน ประจำปี {{ $year + 543 }}</div>
                <div class="table-responsive">
                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">เดือน</th>
                                <th class="text-end">รายรับ (School)</th>
                                <th class="text-end">รายรับ (RV)</th>
                                <th class="text-end">รวมรายรับ</th>
                                <th class="text-end">รายจ่าย (PV)</th>
                                <th class="text-end">รายจ่าย (Payroll)</th>
                                <th class="text-end">รวมรายจ่าย</th>
                                <th class="text-end">กำไร/ขาดทุน</th>
                                <th class="text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalYearRevenue = 0;
                                $totalYearExpense = 0;
                                $totalYearProfit = 0;
                            @endphp
                            @foreach($monthlyPerformance as $month => $data)
                            @php
                                $totalYearRevenue += $data['total_revenue'];
                                $totalYearExpense += $data['total_expense'];
                                $totalYearProfit += $data['profit'];
                            @endphp
                            <tr>
                                <td class="text-center">
                                    {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }}
                                </td>
                                <td class="text-end text-success">{{ number_format($data['school_revenue'], 2) }}</td>
                                <td class="text-end text-success">{{ number_format($data['rv_revenue'], 2) }}</td>
                                <td class="text-end font-weight-bold text-success">{{ number_format($data['total_revenue'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($data['pv_expense'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($data['payroll_expense'], 2) }}</td>
                                <td class="text-end font-weight-bold text-danger">{{ number_format($data['total_expense'], 2) }}</td>
                                <td class="text-end font-weight-bold {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($data['profit'], 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('performance.monthly', array_merge(request()->query(), ['year' => $year, 'month' => $month])) }}" class="btn btn-primary btn-sm">
                                        รายละเอียด
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td class="text-center">รวมทั้งปี</td>
                                <td colspan="2" class="text-end"></td>
                                <td class="text-end text-success">{{ number_format($totalYearRevenue, 2) }}</td>
                                <td colspan="2" class="text-end"></td>
                                <td class="text-end text-danger">{{ number_format($totalYearExpense, 2) }}</td>
                                <td class="text-end {{ $totalYearProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($totalYearProfit, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
