@extends('layouts.main_template')

@section('content')
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-graph1 icon-gradient bg-midnight-bloom"></i>
                </div>
                <div>รายละเอียดผลประกอบการ (Monthly Breakdown)
                    <div class="page-title-subheading">{{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} {{ $year + 543 }}</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('performance.index', ['year' => $year]) }}" class="btn btn-secondary">
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
</div>
@endsection
