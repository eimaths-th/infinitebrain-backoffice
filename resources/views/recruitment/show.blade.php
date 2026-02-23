@extends('layouts.main_template')

@section('style')
    <style>
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
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

        .log-list {
            list-style: none;
            padding-left: 0;
            position: relative;
        }

        .log-item {
            padding-left: 30px;
            position: relative;
            margin-bottom: 20px;
        }

        .log-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #dee2e6;
            z-index: 1;
        }

        .log-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 20px;
            width: 2px;
            height: calc(100% + 10px);
            background-color: #dee2e6;
        }

        .log-item:last-child::after {
            display: none;
        }

        .log-time {
            font-size: 11px;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-user icon-gradient bg-sunny-morning"></i>
                    </div>
                    <div>
                        รายละเอียดผู้สมัคร: {{ $applicant->GetFullName() }}
                        <div class="page-title-subheading">สถานะปัจจุบัน:
                            <span
                                class="status-badge status-{{ $applicant->status }}">{{ $applicant->GetStatusLabel() }}</span>
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('recruitment.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับไปรายการ
                    </a>
                </div>
            </div>
        </div>

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

        <div class="col-md-7">
            <!-- Applicant Details Card -->
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title text-primary">ข้อมูลส่วนตัว</h5>
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <strong>ชื่อ-นามสกุล:</strong> {{ $applicant->GetFullName() }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>ตำแหน่งที่สมัคร:</strong> {{ $applicant->position_applied ?? '-' }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>อีเมล:</strong> {{ $applicant->email ?? '-' }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>เบอร์โทรศัพท์:</strong> {{ $applicant->phone ?? '-' }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>วันที่อัปโหลด:</strong> {{ $applicant->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>โดย:</strong> {{ $applicant->admin->name ?? 'System' }}
                        </div>
                    </div>

                    <hr>

                    <h5 class="card-title text-primary">Resume / เอกสารประกอบ</h5>
                    @if ($applicant->resume_path)
                        <div class="ratio ratio-16x9 border" style="height: 600px;">
                            <iframe src="{{ asset('storage/' . $applicant->resume_path) }}" allow="autoplay"></iframe>
                        </div>
                        <div class="mt-2 text-center">
                            <a href="{{ asset('storage/' . $applicant->resume_path) }}" target="_blank"
                                class="btn btn-outline-info">
                                <i class="fa fa-external-link-alt"></i> เปิดหน้าต่างใหม่ / ดาวน์โหลด
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning">ไม่พบไฟล์เอกสาร</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <!-- Action Cards -->
            @can('recruitment.review')
                @if ($applicant->status == 'pending')
                    <div class="main-card mb-3 card border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">ตรวจสอบผู้สมัคร (Reviewer)</h5>
                            <form action="{{ route('recruitment.review', $applicant->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label d-block">ตัดสินใจติดตามต่อหรือไม่? <span
                                            class="text-danger">*</span></label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="follow_up" id="follow_yes"
                                            value="yes" required>
                                        <label class="form-check-label text-success fw-bold" for="follow_yes">ติดตาม (Follow
                                            Up)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="follow_up" id="follow_no"
                                            value="no" required>
                                        <label class="form-check-label text-danger fw-bold" for="follow_no">ไม่ติดตาม (No Follow
                                            Up)</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">หมายเหตุการตรวจสอบ</label>
                                    <textarea name="note" class="form-control" rows="3"
                                        placeholder="ระบุความเห็นเบื้องต้น สังกัดที่แนะนำ หรือเหตุผลที่ไม่ติดตามต่อ..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-save"></i> บันทึกผลการตรวจสอบ
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            @can('recruitment.verify')
                @if ($applicant->status == 'reviewed')
                    <div class="main-card mb-3 card border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success">การอนุมัติขั้นสุดท้าย (Manager)</h5>
                            <div class="alert alert-info py-2">
                                <strong>ความเห็น Reviewer:</strong> {{ $applicant->reviewer_note ?? '-' }}<br>
                                <strong>การติดตาม:</strong>
                                @if ($applicant->follow_up == 'yes')
                                    <span class="text-success fw-bold">ติดตาม</span>
                                @else
                                    <span class="text-danger fw-bold">ไม่ติดตาม</span>
                                @endif
                            </div>
                            <form action="{{ route('recruitment.verify', $applicant->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">ตัดสินใจผลการสมัคร <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="">-- เลือกสถานะ --</option>
                                        <option value="verified">Verified (ผ่านการตรวจสอบ)</option>
                                        <option value="rejected">Rejected (ไม่ผ่าน)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">หมายเหตุ/คำสั่งการ</label>
                                    <textarea name="note" class="form-control" rows="3"
                                        placeholder="ระบุคำสั่งการเพิ่มเติม หรือเหตุผลการตัดสินใจ..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa fa-check-double"></i> บันทึกผลการอนุมัติ
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            <!-- Result Card for completed process -->
            @if (in_array($applicant->status, ['verified', 'rejected']))
                <div
                    class="main-card mb-3 card {{ $applicant->status == 'verified' ? 'border-success' : 'border-danger' }}">
                    <div class="card-body">
                        <h5 class="card-title {{ $applicant->status == 'verified' ? 'text-success' : 'text-danger' }}">
                            ผลการดำเนินการ
                        </h5>
                        <div class="mb-2">
                            <strong>สถานะ:</strong> {{ $applicant->GetStatusLabel() }}
                        </div>
                        <div class="mb-2">
                            <strong>ผู้อนุมัติ:</strong> {{ $applicant->manager->name ?? 'System' }}
                        </div>
                        <div class="mb-0">
                            <strong>หมายเหตุ Manager:</strong><br>
                            <p class="mb-0 text-muted italic">{{ $applicant->manager_note ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- History Logs Card -->
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">ประวัติการดำเนินการ (Audit Logs)</h5>
                    <ul class="log-list mt-3">
                        @foreach ($applicant->logs()->orderBy('created_at', 'desc')->get() as $log)
                            <li class="log-item">
                                <div class="log-time">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                <div class="fw-bold">{{ $log->user->name ?? 'System' }}</div>
                                <div class="small">
                                    {{ $log->action }}
                                    @if ($log->to_status)
                                        <span class="text-muted">-> {{ $log->to_status }}</span>
                                    @endif
                                </div>
                                @if ($log->note)
                                    <div class="mt-1 small border-start ps-2 italic text-muted">"{{ $log->note }}"
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
