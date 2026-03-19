@extends('layouts.main_template')

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
                        $groupMap = [
                            'EL' => 'Eye Level',
                            'IF' => 'Infinite Brain',
                            'META' => 'META Knowledge',
                            'EiMaths' => 'eiMaths',
                        ];
                        $titles = [];
                        foreach(($selectedGroups ?? []) as $g) {
                            $titles[] = $groupMap[$g] ?? $g;
                        }
                        $displayTitle = implode(' & ', $titles);
                    @endphp
                    รายละเอียดผลประกอบการ {{ $displayTitle }}
                    <div class="page-title-subheading">
                        {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} {{ $year + 543 }}
                    </div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('performance.monthly.print', array_merge(request()->query(), ['year' => $year, 'month' => $month])) }}" target="_blank" class="btn btn-info">
                    <i class="fa fa-print"></i> พิมพ์รายงาน
                </a>
                <a href="{{ route('performance.index', array_merge(request()->query(), ['year' => $year])) }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> กลับไปหน้าภาพรวม
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-4">
            <div class="card mb-3 widget-content bg-midnight-bloom">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading">รวมรายรับ</div>
                        <div class="widget-subheading">ทั้งระบบ</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-white"><span>{{ number_format($data['total_revenue'], 2) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card mb-3 widget-content bg-arielle-smile">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading">รวมรายจ่าย</div>
                        <div class="widget-subheading">ทั้งระบบ</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-white"><span>{{ number_format($data['total_expense'], 2) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card mb-3 widget-content {{ $data['profit'] >= 0 ? 'bg-happy-green' : 'bg-love-kiss' }}">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading">กำไร / ขาดทุน</div>
                        <div class="widget-subheading">สุทธิ</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-white"><span>{{ number_format($data['profit'], 2) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Breakdown -->
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">รายละเอียดรายรับ</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left">
                                        <div class="widget-heading">School Receipts</div>
                                        <div class="widget-subheading">จากระบบลงทะเบียนเรียน</div>
                                    </div>
                                    <div class="widget-content-right">
                                        <div class="font-size-xlg text-success">{{ number_format($data['school_revenue'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left">
                                        <div class="widget-heading">Receipt Vouchers</div>
                                        <div class="widget-subheading">ใบสำคัญรับอื่นๆ</div>
                                    </div>
                                    <div class="widget-content-right">
                                        <div class="font-size-xlg text-success">{{ number_format($data['rv_revenue'], 2) }}</div>
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
                <div class="card-header">รายละเอียดรายจ่าย</div>
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
                                        <div class="font-size-xlg text-danger">{{ number_format($data['pv_expense'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left">
                                        <div class="widget-heading">Payroll</div>
                                        <div class="widget-subheading">วิชาการ + สำนักงาน (Paid)</div>
                                    </div>
                                    <div class="widget-content-right">
                                        <div class="font-size-xlg text-danger">{{ number_format($data['payroll_expense'], 2) }}</div>
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
                <div class="card-header-tab card-header">
                    <div class="card-header-title font-size-lg text-capitalize font-weight-normal">
                        <i class="header-icon pe-7s-notebook icon-gradient bg-happy-itmeo"></i>
                        รายละเอียดรายรับ (Revenue Details)
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a data-bs-toggle="tab" href="#tab-school-revenue" class="nav-link active">School Receipts</a></li>
                        <li class="nav-item"><a data-bs-toggle="tab" href="#tab-rv-revenue" class="nav-link">Receipt Vouchers</a></li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane active" id="tab-school-revenue" role="tabpanel">
                            <div class="table-responsive">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>กลุ่มวิชา (Subject Group)</th>
                                            <th class="text-center">จำนวนนักเรียน</th>
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
                                                <td class="text-end text-success font-weight-bold">{{ number_format($item->total_amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">ไม่มีข้อมูล</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-rv-revenue" role="tabpanel">
                            <div class="table-responsive">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Voucher No.</th>
                                            <th>Received From</th>
                                            <th>Remark</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data['details']['rv_items'] as $item)
                                            <tr>
                                                <td>{{ $item->voucher_date->format('Y-m-d') }}</td>
                                                <td>
                                                    <a href="{{ route('receipt-voucher.show', $item->id) }}" target="_blank">
                                                        {{ $item->voucher_no }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @php $from = $item->payee ? $item->payee->name : $item->received_from; @endphp
                                                    {{ $from ?: '-' }}
                                                </td>
                                                <td>{{ $item->remark }}</td>
                                                <td class="text-end">{{ number_format($item->net_amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center">No data available</td></tr>
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
                <div class="card-header-tab card-header">
                    <div class="card-header-title font-size-lg text-capitalize font-weight-normal">
                        <i class="header-icon pe-7s-cash icon-gradient bg-mixed-hopes"></i>
                        รายละเอียดรายจ่าย (Expense Details)
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a data-bs-toggle="tab" href="#tab-pv-expense" class="nav-link active">Payment Vouchers</a></li>
                        <li class="nav-item"><a data-bs-toggle="tab" href="#tab-payroll-expense" class="nav-link">Payroll</a></li>
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
                                            @foreach($voucher->expenseItems as $item)
                                                <tr>
                                                    <td>{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                                    <td>
                                                        <a href="{{ route('payment-voucher.show', $voucher->id) }}" target="_blank">
                                                            {{ $voucher->voucher_no }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $voucher->branch_code }}</td>
                                                    <td>{{ \App\Models\PaymentVoucher::$categoryLabels[$voucher->category] ?? 'อื่นๆ' }}</td>
                                                    <td>
                                                        <div class="font-weight-bold">{{ $item->description }}</div>
                                                        <div class="small text-muted">{{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }}</div>
                                                    </td>
                                                    <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr><td colspan="5" class="text-center">No data available</td></tr>
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
                                                <td>{{ $item->payrollPeriod->month }}/{{ $item->payrollPeriod->year }}</td>
                                                <td class="text-end font-weight-bold">{{ number_format($item->net_pay, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">ไม่มีข้อมูล</td></tr>
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

