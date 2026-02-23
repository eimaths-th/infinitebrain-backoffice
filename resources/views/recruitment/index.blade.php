@extends('layouts.main_template')

@section('style')
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-reviewed {
            background-color: #007bff;
            color: white;
        }

        .status-verified {
            background-color: #28a745;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .follow-up-yes {
            color: #28a745;
            font-weight: bold;
        }

        .follow-up-no {
            color: #dc3545;
            font-weight: bold;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <p class="mb-0">{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <p class="mb-0">{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-users icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        ระบบรับสมัครงาน (Recruitment)
                        <div class="page-title-subheading">จัดการรายชื่อผู้สมัครและสถานะการตรวจสอบ</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    @can('recruitment.upload')
                        <a href="{{ route('recruitment.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> เพิ่มผู้สมัครใหม่
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" action="{{ route('recruitment.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอตรวจสอบ
                                </option>
                                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>ตรวจสอบแล้ว
                                </option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>อนุมัติแล้ว
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ
                                </option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">ค้นหา</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="ชื่อ-นามสกุล/อีเมล/เบอร์โทร/ตำแหน่ง..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-search"></i> ค้นหา
                            </button>
                            <a href="{{ route('recruitment.index') }}" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i> รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ตำแหน่งที่สมัคร</th>
                                <th>ติดต่อ</th>
                                <th>ติดตาม</th>
                                <th>สถานะ</th>
                                <th>ผู้อัปโหลด</th>
                                <th width="10%">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applicants as $index => $applicant)
                                <tr>
                                    <td>{{ $applicants->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('recruitment.show', $applicant->id) }}" class="fw-bold">
                                            {{ $applicant->GetFullName() }}
                                        </a>
                                    </td>
                                    <td>{{ $applicant->position_applied }}</td>
                                    <td>
                                        <div class="small">
                                            <i class="fa fa-envelope text-muted"></i> {{ $applicant->email }}<br>
                                            <i class="fa fa-phone text-muted"></i> {{ $applicant->phone }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($applicant->follow_up == 'yes')
                                            <span class="follow-up-yes"><i class="fa fa-check-circle"></i> ติดตาม</span>
                                        @elseif($applicant->follow_up == 'no')
                                            <span class="follow-up-no"><i class="fa fa-times-circle"></i> ไม่ติดตาม</span>
                                        @else
                                            <span class="text-muted italic small">ยังไม่ระบุ</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $applicant->status }}">
                                            {{ $applicant->GetStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $applicant->admin->name ?? 'System' }}</td>
                                    <td>
                                        <a href="{{ route('recruitment.show', $applicant->id) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        ไม่พบข้อมูลผู้สมัคร
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $applicants->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
