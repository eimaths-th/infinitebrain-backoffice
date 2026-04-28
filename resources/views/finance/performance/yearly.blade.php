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
                    <div>
                        @php
                            $titles = [];
                            foreach ($selectedGroups ?? [] as $g) {
                                $titles[] = $groupMap[$g] ?? $g;
                            }
                            $displayTitle = implode(' & ', $titles);
                        @endphp
                        รายละเอียดผลประกอบการรายปี {{ $displayTitle }}
                        <div class="page-title-subheading">
                            ประจำปี {{ $year + 543 }} (รวมทั้งปี)
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('performance.yearly.print', array_merge(request()->query(), ['year' => $year])) }}"
                        target="_blank" class="btn btn-info">
                        <i class="fa fa-print"></i> พิมพ์รายงานปี
                    </a>
                    @can('receipt-voucher.evaluation.view')
                    <a href="{{ route('performance.yearly.print-evaluation', array_merge(request()->query(), ['year' => $year])) }}"
                        target="_blank" class="btn btn-success">
                        <i class="fa fa-clipboard"></i> พิมพ์ใบประเมินรายปี
                    </a>
                    @endcan
                    <a href="{{ route('performance.index', array_merge(request()->query(), ['year' => $year])) }}"
                        class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับไปหน้าภาพรวม
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="main-card card">
                    <div class="card-body">
                        <form action="{{ route('performance.yearly', ['year' => $year]) }}" method="GET" class="row align-items-center">
                            <div class="col-md-12">
                                <h6 class="text-muted text-uppercase mb-3" style="letter-spacing: 1px; font-weight: 800;">
                                    <i class="fa fa-sitemap me-2"></i>เลือกกลุ่มสาขาและสาขาที่ต้องการดูผลประกอบการรายปี
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
                                        <i class="fa fa-search me-2"></i>กรองข้อมูลรายปี
                                    </button>
                                    <a href="{{ route('performance.yearly', ['year' => $year]) }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill d-flex align-items-center">
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
            <div class="col-md-6 col-xl-4">
                <div class="card mb-3 widget-content bg-midnight-bloom">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">รวมรายรับทั้งปี</div>
                            <div class="widget-subheading">ทุกแหล่งที่มา</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white">
                                <span>{{ number_format($data['total_revenue'], 2) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card mb-3 widget-content bg-arielle-smile">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">รวมรายจ่ายทั้งปี</div>
                            <div class="widget-subheading">ทุกรายการ</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white">
                                <span>{{ number_format($data['total_expense'], 2) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card mb-3 widget-content {{ $data['profit'] >= 0 ? 'bg-happy-green' : 'bg-love-kiss' }}">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">กำไร / ขาดทุน สุทธิ</div>
                            <div class="widget-subheading">รวมทั้งปี</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white"><span>{{ number_format($data['profit'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Revenue Breakdown -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">สรุปรายรับรายปี</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">School Receipts</div>
                                            <div class="widget-subheading">จากระบบลงทะเบียนเรียน (EL)</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-success">
                                                {{ number_format($data['local_school_revenue'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @if ($data['api_school_revenue'] > 0)
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Eimaths Math Course</div>
                                                <div class="widget-subheading">ข้อมูลจาก Eimaths API</div>
                                            </div>
                                            <div class="widget-content-right">
                                                <div class="font-size-xlg text-success">
                                                    {{ number_format($data['api_school_revenue'], 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Receipt Vouchers</div>
                                            <div class="widget-subheading">ใบสำคัญรับอื่นๆ</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-success">
                                                {{ number_format($data['rv_revenue'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">สรุปรายจ่ายรายปี</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Payment Vouchers</div>
                                            <div class="widget-subheading">ใบสำคัญจ่าย (เสร็จสิ้น)</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-danger">
                                                {{ number_format($data['pv_expense'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Payroll</div>
                                            <div class="widget-subheading">เงินเดือน (Paid)</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-danger">
                                                {{ number_format($data['payroll_expense'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">รายการหักประจำปี</div>
                                            <div class="widget-subheading">หัก ณ ที่จ่าย และรายการหักอื่นๆ สะสมทั้งปี</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-info">
                                                {{ number_format($data['pv_deductions'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header-tab card-header d-flex justify-content-between align-items-center">
                        <div class="card-header-title font-size-lg text-capitalize font-weight-normal">
                            <i class="header-icon pe-7s-notebook icon-gradient bg-happy-itmeo"></i>
                            รายละเอียดรายรับทั้งปี (Yearly Revenue Details)
                        </div>
                        <div class="btn-actions-pane-right">
                            <a href="{{ route('performance.yearly.print-revenue', array_merge(request()->query(), ['year' => $year])) }}"
                                target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fa fa-print"></i> พิมพ์รายละเอียดรายรับ
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a data-bs-toggle="tab" href="#tab-school-revenue"
                                    class="nav-link active">School Receipts</a></li>
                            @if (!empty($data['details']['api_revenue_list']))
                                <li class="nav-item"><a data-bs-toggle="tab" href="#tab-api-revenue"
                                        class="nav-link">Math Course (API)</a></li>
                            @endif
                            <li class="nav-item"><a data-bs-toggle="tab" href="#tab-rv-revenue" class="nav-link">Receipt
                                    Vouchers</a></li>
                        </ul>
                        <div class="tab-content mt-3">
                            <div class="tab-pane active" id="tab-school-revenue" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>กลุ่มวิชา (Subject Group)</th>
                                                <th class="text-center">จำนวนนักเรียน (สะสมรายเดือน)</th>
                                                <th class="text-center">จำนวนใบเสร็จ</th>
                                                <th class="text-end">ยอดรวม (Amount)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data['details']['school_groups'] as $item)
                                                <tr>
                                                    <td>{{ $item->title }} ({{ $item->group }})</td>
                                                    <td class="text-center">{{ number_format($item->student_count) }}</td>
                                                    <td class="text-center">{{ number_format($item->receipt_count) }}</td>
                                                    <td class="text-end text-success font-weight-bold">
                                                        {{ number_format($item->total_amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">ไม่มีข้อมูล</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if (!empty($data['details']['api_revenue_list']))
                                <div class="tab-pane" id="tab-api-revenue" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>สาขา (Branch)</th>
                                                    <th>รหัสสาขา</th>
                                                    <th>แหล่งที่มา</th>
                                                    <th class="text-end">ยอดรายได้รวมทั้งปี (Amount)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data['details']['api_revenue_list'] as $item)
                                                    <tr>
                                                        <td>{{ $item->branch_name }}</td>
                                                        <td><span style="background-color: #ff8000 !important; color: #ffffff !important; font-weight: 800; padding: 2px 5px; border-radius: 4px; font-size: 11px; display: inline-block; white-space: nowrap;">{{ $item->branch_code }}</span>
                                                        </td>
                                                        <td><small class="text-muted">{{ $item->source }}</small></td>
                                                        <td class="text-end text-success font-weight-bold">
                                                            {{ number_format($item->revenue, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            <div class="tab-pane" id="tab-rv-revenue" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Voucher No.</th>
                                                <th>Received From</th>
                                                <th>Description</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data['details']['rv_items'] as $voucher)
                                                @foreach ($voucher->receiptItems as $item)
                                                    <tr>
                                                        <td>{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                                        <td>
                                                            <a href="{{ route('receipt-voucher.show', $voucher->id) }}"
                                                                target="_blank">
                                                                {{ $voucher->voucher_no }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            @php $from = $voucher->payee ? $voucher->payee->name : $voucher->received_from; @endphp
                                                            {{ $from ?: '-' }}
                                                        </td>
                                                        <td>{{ $item->description }}</td>
                                                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header-tab card-header d-flex justify-content-between align-items-center">
                        <div class="card-header-title font-size-lg text-capitalize font-weight-normal">
                            <i class="header-icon pe-7s-cash icon-gradient bg-mixed-hopes"></i>
                            รายละเอียดรายจ่ายทั้งปี (Yearly Expense Details)
                        </div>
                        <div class="btn-actions-pane-right">
                            <a href="{{ route('performance.yearly.print-expense', array_merge(request()->query(), ['year' => $year])) }}"
                                target="_blank" class="btn btn-outline-danger btn-sm">
                                <i class="fa fa-print"></i> พิมพ์รายละเอียดรายจ่าย
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a data-bs-toggle="tab" href="#tab-pv-expense"
                                    class="nav-link active">Payment Vouchers</a></li>
                            <li class="nav-item"><a data-bs-toggle="tab" href="#tab-payroll-expense"
                                    class="nav-link">Payroll</a></li>
                        </ul>
                        <div class="tab-content mt-3">
                            <div class="tab-pane active" id="tab-pv-expense" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Voucher No.</th>
                                                <th>Branch</th>
                                                <th>Category</th>
                                                <th>Pay To</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data['details']['pv_items'] as $voucher)
                                                @foreach ($voucher->expenseItems as $item)
                                                    <tr>
                                                        <td>{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                                        <td>
                                                            <a href="{{ route('payment-voucher.show', $voucher->id) }}"
                                                                target="_blank">
                                                                {{ $voucher->voucher_no }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $voucher->branch_code }}</td>
                                                        <td>{{ \App\Models\PaymentVoucher::getAllCategoryLabels()[$voucher->category] ?? 'อื่นๆ' }}
                                                        </td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $item->description }}</div>
                                                            <div class="small text-muted">
                                                                {{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }}
                                                            </div>
                                                        </td>
                                                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab-payroll-expense" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ชื่อ-นามสกุล</th>
                                                <th>แผนก (Department)</th>
                                                <th>งวด (Period)</th>
                                                <th class="text-end">ยอดสุทธิ (Net Pay)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data['details']['payroll_items'] as $item)
                                                <tr>
                                                    <td>{{ $item->user->name ?? $item->user_id }}</td>
                                                    <td>{{ $item->user->department ?? '-' }}</td>
                                                    <td>{{ $item->payrollPeriod->month }}/{{ $item->payrollPeriod->year }}
                                                    </td>
                                                    <td class="text-end font-weight-bold">
                                                        {{ number_format($item->net_pay, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">ไม่มีข้อมูล</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
