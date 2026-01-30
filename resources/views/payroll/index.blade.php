@extends('layouts.main_template')

@section('style')
    <style>
        .period-card {
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .period-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
        }

        .summary-item {
            text-align: center;
            padding: 10px;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .summary-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-cash icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            ระบบเงินเดือน
                            <div class="page-title-subheading">จัดการงวดเงินเดือนและค่าจ้าง</div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('payroll.create') }}" class="btn-shadow btn btn-primary">
                            <i class="fas fa-plus me-1"></i> สร้างงวดใหม่
                        </a>
                    </div>
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

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">ปี</label>
                            <select name="year" class="form-select">
                                <option value="">ทั้งหมด</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="">ทั้งหมด</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>ร่าง</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                    กำลังประมวลผล</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว
                                </option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>จ่ายแล้ว</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search me-1"></i> ค้นหา
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Periods List -->
            <div class="row">
                @forelse($periods as $period)
                    <div class="col-md-4 mb-4">
                        <div class="card period-card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $period->period_name }}</h5>
                                {!! $period->GetStatusBadge() !!}
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">พนักงาน</small>
                                        <div class="fw-bold">{{ number_format($period->employee_count) }} คน</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">วันจ่าย</small>
                                        <div class="fw-bold">{{ $period->payment_date?->format('d/m/Y') ?? '-' }}</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted">รายได้รวม</small>
                                        <div class="text-success fw-bold">{{ number_format($period->total_income, 2) }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">หักรวม</small>
                                        <div class="text-danger fw-bold">{{ number_format($period->total_deduction, 2) }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">สุทธิ</small>
                                        <div class="text-primary fw-bold">{{ number_format($period->total_net_pay, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> ดูรายละเอียด
                                </a>
                                @if ($period->CanEdit())
                                    <form action="{{ route('payroll.destroy', $period->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('ยืนยันการลบงวดนี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5>ยังไม่มีงวดเงินเดือน</h5>
                                <p class="text-muted">คลิกปุ่ม "สร้างงวดใหม่" เพื่อเริ่มต้น</p>
                                <a href="{{ route('payroll.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> สร้างงวดใหม่
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $periods->links() }}
            </div>
        </div>
    @endsection
