@extends('layouts.main_template')
@section('style')
<style>
    .filter-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.25rem;
        background: #fff;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .filter-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #3f6ad8;
    }
    .group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        color: #3f6ad8;
        cursor: pointer;
    }
    .branch-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.75rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 1rem;
    }
    .custom-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .toggle-icon {
        transition: transform 0.3s ease;
    }
    .toggle-icon.rotated {
        transform: rotate(180deg);
    }
    .btn-filter {
        padding: 0.75rem 1.5rem;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
</style>
@endsection

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
                <a href="{{ route('performance.yearly', array_merge(request()->query(), ['year' => $year])) }}" class="btn-shadow btn btn-success me-2">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-file-invoice-dollar fa-w-20"></i>
                    </span>
                    ดูรายละเอียดรายปี
                </a>
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
                        <div class="col-md-12">
                            <h6 class="text-muted text-uppercase mb-3" style="letter-spacing: 1px; font-weight: 800;">
                                <i class="fa fa-sitemap me-2"></i>เลือกกลุ่มสาขาและสาขาที่ต้องการดูผลประกอบการ
                            </h6>
                            <div class="row g-4">
                                @foreach($groupsWithBranches as $group)
                                    <div class="col-lg-6 col-xl-3">
                                        <div class="filter-card">
                                            <div class="group-header" data-target="branches_{{ $group->code }}">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input group-checkbox custom-checkbox" type="checkbox" name="groups[]" value="{{ $group->code }}"
                                                        id="group_{{ $group->code }}" data-group="{{ $group->code }}"
                                                        {{ in_array($group->code, $selectedGroups) ? 'checked' : '' }}>
                                                    <label class="form-check-label ps-2 fs-6 mb-0" for="group_{{ $group->code }}" style="cursor: pointer;">
                                                        {{ $group->name }}
                                                    </label>
                                                </div>
                                                <i class="fa fa-chevron-down text-muted toggle-icon {{ in_array($group->code, $selectedGroups) ? 'rotated' : '' }}"></i>
                                            </div>

                                            <div class="branch-grid mt-2" id="branches_{{ $group->code }}"
                                                 style="display: {{ in_array($group->code, $selectedGroups) ? 'grid' : 'none' }};">
                                                @foreach($group->branches as $branch)
                                                    <div class="form-check">
                                                        <input class="form-check-input branch-checkbox group_{{ $group->code }}" type="checkbox"
                                                            name="branches[]" value="{{ $branch->code }}" id="branch_{{ $branch->code }}"
                                                            {{ in_array($branch->code, $selectedBranches) ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="branch_{{ $branch->code }}" style="cursor: pointer;">
                                                            {{ $branch->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-center gap-3">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm btn-filter">
                                    <i class="fa fa-search me-2"></i>กรองข้อมูล
                                </button>
                                <a href="{{ route('performance.index', ['year' => $year]) }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill d-flex align-items-center">
                                    <i class="fa fa-undo me-2"></i>คืนค่าเริ่มต้น
                                </a>
                            </div>
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

@section('script')
<script>
    $(document).ready(function() {
        // Toggle visibility click on header title/icon (avoid checkbox click)
        $('.group-header').on('click', function(e) {
            if ($(e.target).closest('.form-check').length) return;

            const targetId = $(this).data('target');
            const $branchList = $('#' + targetId);
            const $icon = $(this).find('.toggle-icon');

            $branchList.slideToggle(300, function() {
                if ($branchList.is(':visible')) {
                    $branchList.css('display', 'grid');
                }
            });
            $icon.toggleClass('rotated');
        });

        // Toggle on checkbox change
        $('.group-checkbox').on('change', function() {
            const groupCode = $(this).data('group');
            const $branchList = $('#branches_' + groupCode);
            const $branches = $('.group_' + groupCode);
            const $icon = $(this).closest('.group-header').find('.toggle-icon');

            if ($(this).is(':checked')) {
                $branchList.slideDown(300, function() {
                    $(this).css('display', 'grid');
                });
                $icon.addClass('rotated');
                // Auto check first branch if none are checked
                const checkedInGroup = $branches.filter(':checked').length > 0;
                if (!checkedInGroup && $branches.length > 0) {
                    $branches.first().prop('checked', true);
                }
            } else {
                $branchList.slideUp(300);
                $icon.removeClass('rotated');
                $branches.prop('checked', false);
            }
        });
    });
</script>
@endsection
