@extends('layouts.main_template')

@section('style')
    <style>
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .employee-type-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .payroll-table th {
            background-color: #f1f3f4;
            font-weight: 600;
            white-space: nowrap;
        }

        .payroll-table td {
            vertical-align: middle;
        }

        .action-btn {
            padding: 5px 10px;
            font-size: 12px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-calendar-alt me-2"></i>{{ $period->period_name }}
                </h4>
                <p class="text-muted mb-0">
                    งวดวันที่ {{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}
                    | วันจ่าย: {{ $period->payment_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Summary Card -->
        <div class="card summary-card mb-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="h4 mb-0">{{ number_format($period->employee_count) }}</div>
                        <small>พนักงาน</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 mb-0">{{ number_format($period->total_income, 2) }}</div>
                        <small>รายได้รวม</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 mb-0">{{ number_format($period->total_deduction, 2) }}</div>
                        <small>หักรวม</small>
                    </div>
                    <div class="col-md-2">
                        <div class="h4 mb-0">{{ number_format($period->total_net_pay, 2) }}</div>
                        <small>จ่ายสุทธิ</small>
                    </div>
                    <div class="col-md-2">
                        {!! $period->GetStatusBadge() !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        @if ($period->CanEdit())
            <div class="card mb-4">
                <div class="card-body d-flex gap-2 flex-wrap">
                    <form action="{{ route('payroll.generate', $period->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('ยืนยันการดึงพนักงานทั้งหมดเข้างวด?')">
                            <i class="fas fa-users me-1"></i> ดึงพนักงานเข้างวด
                        </button>
                    </form>

                    <a href="{{ route('payroll.import', $period->id) }}" class="btn btn-info">
                        <i class="fas fa-file-excel me-1"></i> Import Excel
                    </a>

                    <form action="{{ route('payroll.sync-attendance', $period->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info"
                            onclick="return confirm('ยืนยันการดึงข้อมูลเวลาเข้า-ออกงาน?')">
                            <i class="fas fa-clock me-1"></i> Sync Attendance
                        </button>
                    </form>

                    <a href="{{ route('payroll.bulk-edit', $period->id) }}" class="btn btn-warning text-dark">
                        <i class="fas fa-edit me-1"></i> แก้ไขหลายคน (Bulk Edit)
                    </a>

                    @if ($period->payrolls->count() > 0)
                        <form action="{{ route('payroll.approve', $period->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary"
                                onclick="return confirm('ยืนยันการอนุมัติงวดนี้?')">
                                <i class="fas fa-check me-1"></i> อนุมัติ
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @elseif($period->status === 'approved')
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('payroll.paid', $period->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('ยืนยันว่าจ่ายเงินเดือนแล้ว?')">
                            <i class="fas fa-money-bill-wave me-1"></i> บันทึกว่าจ่ายแล้ว
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Fulltime Employees -->
        @if ($fulltime_payrolls->count() > 0)
            <div class="card mb-4">
                <div class="card-header employee-type-header">
                    <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>พนักงานประจำ ({{ $fulltime_payrolls->count() }}
                        คน)</h6>
                </div>
                <div class="card-body p-0">
                    @include('payroll._payroll_table', [
                        'payrolls' => $fulltime_payrolls,
                        'period' => $period,
                    ])
                </div>
            </div>
        @endif

        <!-- Part-time Employees -->
        @if ($parttime_payrolls->count() > 0)
            <div class="card mb-4">
                <div class="card-header employee-type-header">
                    <h6 class="mb-0"><i class="fas fa-user-clock me-2"></i>พนักงานพาร์ทไทม์
                        ({{ $parttime_payrolls->count() }} คน)</h6>
                </div>
                <div class="card-body p-0">
                    @include('payroll._payroll_table', [
                        'payrolls' => $parttime_payrolls,
                        'period' => $period,
                    ])
                </div>
            </div>
        @endif

        <!-- Contract Employees -->
        @if ($contract_payrolls->count() > 0)
            <div class="card mb-4">
                <div class="card-header employee-type-header">
                    <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>พนักงานสัญญาจ้าง
                        ({{ $contract_payrolls->count() }} คน)</h6>
                </div>
                <div class="card-body p-0">
                    @include('payroll._payroll_table', [
                        'payrolls' => $contract_payrolls,
                        'period' => $period,
                    ])
                </div>
            </div>
        @endif

        @if ($period->payrolls->count() == 0)
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>ยังไม่มีข้อมูลพนักงานในงวดนี้</h5>
                    <p class="text-muted">กดปุ่ม "ดึงพนักงานเข้างวด" เพื่อเพิ่มพนักงานทั้งหมด</p>
                </div>
            </div>
        @endif
    </div>
@endsection
