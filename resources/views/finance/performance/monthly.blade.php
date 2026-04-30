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
                            $titles = [];
                            foreach ($selectedGroups ?? [] as $g) {
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
                    <a href="{{ route('performance.monthly.print', array_merge(request()->query(), ['year' => $year, 'month' => $month])) }}"
                        target="_blank" class="btn btn-info">
                        <i class="fa fa-print"></i> พิมพ์รายงาน
                    </a>
                    @can('receipt-voucher.evaluation.view')
                    <a href="{{ route('performance.monthly.print-evaluation', array_merge(request()->query(), ['year' => $year, 'month' => $month])) }}"
                        target="_blank" class="btn btn-success">
                        <i class="fa fa-clipboard"></i> พิมพ์ใบประเมิน
                    </a>
                    <a href="{{ route('performance.monthly.export-evaluation', array_merge(request()->query(), ['year' => $year, 'month' => $month])) }}"
                        class="btn btn-outline-success">
                        <i class="fa fa-file-excel"></i> Export Excel
                    </a>
                    @endcan
                    <a href="{{ route('performance.index', array_merge(request()->query(), ['year' => $year])) }}"
                        class="btn btn-secondary">
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
                            <div class="widget-heading">รวมรายจ่าย</div>
                            <div class="widget-subheading">ทั้งระบบ</div>
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
                            <div class="widget-heading">กำไร / ขาดทุน</div>
                            <div class="widget-subheading">สุทธิ</div>
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
                    <div class="card-header">รายละเอียดรายรับ</div>
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
                                            <div class="widget-subheading">วิชาการ + สำนักงาน (Paid)</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-danger">
                                                {{ number_format($data['payroll_expense'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">รายการหักประจำเดือน</div>
                                            <div class="widget-subheading">รายการหักจากใบสำคัญจ่าย และ Payroll (ประกันสังคม/ภาษี)</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="font-size-xlg text-info">
                                                {{ number_format($data['pv_deductions'] + $data['payroll_deductions'], 2) }}</div>
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
                            รายละเอียดรายรับ (Revenue Details)
                        </div>
                        <a href="{{ route('performance.monthly.print-revenue', array_merge(request()->query(), ['year' => $year, 'month' => $month])) }}"
                            target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fa fa-print"></i> พิมพ์รายรับ
                        </a>
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
                                                    <th class="text-end">ยอดรายได้ (Amount)</th>
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
                            รายละเอียดรายจ่าย (Expense Details)
                        </div>
                        <button type="button" onclick="printExpenseSelection()" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-print"></i> พิมพ์รายจ่าย
                        </button>
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
        function printExpenseSelection() {
            swal({
                title: "เลือกรูปแบบการพิมพ์",
                text: "คุณต้องการพิมพ์รายงานรายจ่ายแบบใด?",
                icon: "info",
                buttons: {
                    cancel: {
                        text: "ยกเลิก",
                        value: null,
                        visible: true,
                        className: "btn btn-secondary",
                        closeModal: true,
                    },
                    normal: {
                        text: "แบบปกติ",
                        value: "normal",
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: true
                    },
                    evaluation: {
                        text: "แบบประเมิน",
                        value: "evaluation",
                        visible: true,
                        className: "btn btn-info",
                        closeModal: true
                    }
                }
            }).then((value) => {
                if (value === "normal") {
                    window.open("{!! route('performance.monthly.print-expense', array_merge(request()->query(), ['year' => $year, 'month' => $month])) !!}", "_blank");
                } else if (value === "evaluation") {
                    window.open("{!! route('performance.monthly.print-expense', array_merge(request()->query(), ['year' => $year, 'month' => $month, 'evaluation' => 1])) !!}", "_blank");
                }
            });
        }
    </script>
@endsection
