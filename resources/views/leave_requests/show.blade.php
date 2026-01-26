@extends('layouts.main_template')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="fas fa-file-alt icon-gradient bg-tempting-azure"></i>
                </div>
                <div>รายละเอียดใบลา #{{ $leave_request->id }}
                    <div class="page-title-subheading">{{ $leave_request->GetLeaveTypeName() }}</div>
                </div>
            </div>
            <div class="page-title-actions">
                @if ($leave_request->IsPending() && $leave_request->user_id == Auth::id())
                    <a href="{{ route('leave-requests.edit', $leave_request->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> แก้ไข
                    </a>
                @endif
                <a href="{{ route('leave-requests.export', $leave_request->id) }}" class="btn btn-success">
                    <i class="fas fa-file-word me-1"></i> ดาวน์โหลด Word
                </a>
                <a href="{{ route('leave-requests.pdf', $leave_request->id) }}" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> เปิด PDF / พิมพ์
                </a>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="main-card mb-3 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-info-circle me-2"></i> ข้อมูลใบลา</span>
                    <span class="badge {{ $leave_request->GetStatusBadgeClass() }} fs-6">
                        {{ $leave_request->GetStatusName() }}
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">ผู้ยื่นใบลา:</th>
                            <td>{{ $leave_request->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>ประเภทการลา:</th>
                            <td><span class="badge bg-info">{{ $leave_request->GetLeaveTypeName() }}</span></td>
                        </tr>
                        <tr>
                            <th>วันที่ลา:</th>
                            <td>{{ $leave_request->GetDateRangeText() }}</td>
                        </tr>
                        <tr>
                            <th>จำนวนวัน:</th>
                            <td>{{ $leave_request->total_days }} วัน
                                @if ($leave_request->half_day !== 'none')
                                    <span
                                        class="badge bg-secondary">{{ \App\Models\LeaveRequest::HALF_DAY_OPTIONS[$leave_request->half_day] }}</span>
                                @endif
                            </td>
                        </tr>
                        @if ($leave_request->start_time)
                            <tr>
                                <th>เวลา:</th>
                                <td>{{ $leave_request->start_time }} - {{ $leave_request->end_time }}</td>
                            </tr>
                        @endif
                        @if ($leave_request->location)
                            <tr>
                                <th>สถานที่:</th>
                                <td>{{ $leave_request->location }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>เหตุผล:</th>
                            <td>{{ $leave_request->reason }}</td>
                        </tr>
                        @if ($leave_request->contact_phone)
                            <tr>
                                <th>เบอร์โทรติดต่อ:</th>
                                <td>{{ $leave_request->contact_phone }}</td>
                            </tr>
                        @endif
                        @if ($leave_request->contact_address)
                            <tr>
                                <th>ที่อยู่ติดต่อ:</th>
                                <td>{{ $leave_request->contact_address }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>วันที่ยื่น:</th>
                            <td>{{ $leave_request->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if ($leave_request->employee_signature)
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-signature me-2"></i> ลายเซ็นผู้ยื่นใบลา
                    </div>
                    <div class="card-body text-center">
                        @if ($leave_request->signature_is_image)
                            <img src="{{ Storage::url($leave_request->employee_signature) }}" alt="ลายเซ็น"
                                style="max-height: 150px;" onerror="this.style.display='none'">
                        @elseif(str_starts_with($leave_request->employee_signature, 'data:image'))
                            <img src="{{ $leave_request->employee_signature }}" alt="ลายเซ็น" style="max-height: 150px;">
                        @else
                            <img src="{{ Storage::url($leave_request->employee_signature) }}" alt="ลายเซ็น"
                                style="max-height: 150px;" onerror="this.src='{{ $leave_request->employee_signature }}'">
                        @endif
                    </div>
                </div>
            @endif

            @if ($leave_request->attachments->count() > 0)
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-paperclip me-2"></i> เอกสารแนบ ({{ $leave_request->attachments->count() }} ไฟล์)
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($leave_request->attachments as $attachment)
                                <div class="col-md-6">
                                    <div class="border rounded p-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $attachment->GetFileIcon() }} fa-2x me-3"></i>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="text-truncate fw-bold">{{ $attachment->original_name }}</div>
                                                <small class="text-muted">{{ $attachment->GetFileSizeFormatted() }}</small>
                                            </div>
                                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                        @if ($attachment->IsImage())
                                            <div class="mt-2">
                                                <img src="{{ Storage::url($attachment->file_path) }}"
                                                    class="img-fluid rounded" style="max-height: 150px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="fas fa-check-circle me-2"></i> สถานะการอนุมัติ
                </div>
                <div class="card-body">
                    @if ($leave_request->IsApproved())
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> อนุมัติแล้ว
                        </div>
                        <p><strong>ผู้อนุมัติ:</strong> {{ $leave_request->approver->name ?? '-' }}</p>
                        <p><strong>วันที่อนุมัติ:</strong>
                            {{ $leave_request->approved_at ? $leave_request->approved_at->format('d/m/Y H:i') : '-' }}</p>
                        @if ($leave_request->approver_signature)
                            <div class="text-center border p-2">
                                @if (str_starts_with($leave_request->approver_signature, 'data:image'))
                                    <img src="{{ $leave_request->approver_signature }}" alt="ลายเซ็นผู้อนุมัติ"
                                        style="max-height: 100px;">
                                @elseif(str_starts_with($leave_request->approver_signature, '/storage'))
                                    <img src="{{ $leave_request->approver_signature }}" alt="ลายเซ็นผู้อนุมัติ"
                                        style="max-height: 100px;" onerror="this.style.display='none'">
                                @else
                                    <img src="{{ Storage::url($leave_request->approver_signature) }}"
                                        alt="ลายเซ็นผู้อนุมัติ" style="max-height: 100px;"
                                        onerror="this.src='{{ $leave_request->approver_signature }}'">
                                @endif
                            </div>
                        @endif
                    @elseif($leave_request->IsRejected())
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i> ไม่อนุมัติ
                        </div>
                        <p><strong>ผู้พิจารณา:</strong> {{ $leave_request->approver->name ?? '-' }}</p>
                        <p><strong>วันที่พิจารณา:</strong>
                            {{ $leave_request->approved_at ? $leave_request->approved_at->format('d/m/Y H:i') : '-' }}</p>
                        <p><strong>เหตุผล:</strong> {{ $leave_request->rejection_reason }}</p>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i> รอการพิจารณา
                        </div>
                    @endif
                </div>
            </div>

            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> สถิติการลาปี {{ $current_year + 543 }}
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ประเภท</th>
                                <th class="text-center">โควต้า</th>
                                <th class="text-center">ใช้ไป</th>
                                <th class="text-center">คงเหลือ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leave_stats as $type => $stat)
                                <tr class="{{ $leave_request->leave_type == $type ? 'table-info' : '' }}">
                                    <td>{{ $stat['name'] }}</td>
                                    <td class="text-center">{{ $stat['quota'] }}</td>
                                    <td class="text-center">{{ $stat['used'] }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $stat['remaining'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $stat['remaining'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($leave_request->IsPending() && Auth::user()->hasAnyRole(['super-admin']))
                <div class="main-card mb-3 card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-gavel me-2"></i> พิจารณาใบลา
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">ลายเซ็นผู้อนุมัติ</label>

                            @if (Auth::user()->signature)
                                <div class="alert alert-success py-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            @if (Auth::user()->signature_is_image)
                                                <img src="{{ Storage::url(Auth::user()->signature) }}" alt="ลายเซ็น"
                                                    style="max-height: 60px; background: #fff; padding: 3px; border: 1px solid #ddd; border-radius: 4px;">
                                            @else
                                                <img src="{{ Auth::user()->signature }}" alt="ลายเซ็น"
                                                    style="max-height: 60px; background: #fff; padding: 3px; border: 1px solid #ddd; border-radius: 4px;">
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <small><i class="fas fa-check-circle me-1"></i> ใช้ลายเซ็นที่บันทึกไว้</small>
                                        </div>
                                    </div>
                                    <input type="hidden" id="savedApproverSignature"
                                        value="saved:{{ Auth::user()->signature }}">
                                </div>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        id="toggleApproverNewSignature">
                                        <i class="fas fa-pen me-1"></i> เซ็นใหม่แทน
                                    </button>
                                </div>
                                <div id="approverNewSignatureSection" style="display: none;">
                                @else
                                    <div id="approverNewSignatureSection">
                            @endif
                            <div class="border rounded p-2 bg-white">
                                <canvas id="approverSignaturePad" style="width: 100%; height: 120px;"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-1" id="clearApproverSignature">
                                <i class="fas fa-eraser"></i> ล้าง
                            </button>
                        </div>
                        <input type="hidden" id="useApproverSavedSignature"
                            value="{{ Auth::user()->signature ? '1' : '0' }}">
                    </div>

                    <button type="button" class="btn btn-success w-100 mb-2" id="approveBtn">
                        <i class="fas fa-check me-1"></i> อนุมัติ
                    </button>

                    <hr>

                    <div class="mb-2">
                        <label class="form-label">เหตุผลไม่อนุมัติ</label>
                        <textarea id="rejectionReason" class="form-control" rows="2" placeholder="ระบุเหตุผล"></textarea>
                    </div>
                    <button type="button" class="btn btn-danger w-100" id="rejectBtn">
                        <i class="fas fa-times me-1"></i> ไม่อนุมัติ
                    </button>
                </div>
        </div>
        @endif
    </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            @if ($leave_request->IsPending() && Auth::user()->hasAnyRole(['super-admin', 'admin']))
                var canvas = document.getElementById('approverSignaturePad');
                var signaturePad = null;

                if (canvas) {
                    signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)'
                    });

                    function ResizeCanvas() {
                        var ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext('2d').scale(ratio, ratio);
                        signaturePad.clear();
                    }
                    window.addEventListener('resize', ResizeCanvas);

                    if ($('#approverNewSignatureSection').is(':visible')) {
                        ResizeCanvas();
                    }
                }

                $('#toggleApproverNewSignature').on('click', function() {
                    var section = $('#approverNewSignatureSection');
                    if (section.is(':visible')) {
                        section.hide();
                        $(this).html('<i class="fas fa-pen me-1"></i> เซ็นใหม่แทน');
                        $('#useApproverSavedSignature').val('1');
                    } else {
                        section.show();
                        $(this).html('<i class="fas fa-times me-1"></i> ยกเลิก ใช้ลายเซ็นเดิม');
                        $('#useApproverSavedSignature').val('0');
                        if (signaturePad) {
                            setTimeout(function() {
                                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                                canvas.width = canvas.offsetWidth * ratio;
                                canvas.height = canvas.offsetHeight * ratio;
                                canvas.getContext('2d').scale(ratio, ratio);
                                signaturePad.clear();
                            }, 100);
                        }
                    }
                });

                $('#clearApproverSignature').on('click', function() {
                    if (signaturePad) signaturePad.clear();
                });

                $('#approveBtn').on('click', function() {
                    var useSaved = $('#useApproverSavedSignature').val();
                    var signatureData = '';

                    if (useSaved === '1' && $('#savedApproverSignature').length) {
                        signatureData = $('#savedApproverSignature').val();
                    } else {
                        if (!signaturePad || signaturePad.isEmpty()) {
                            alert('กรุณาเซ็นชื่อผู้อนุมัติ');
                            return;
                        }
                        signatureData = signaturePad.toDataURL();
                    }

                    var btn = $(this);
                    btn.prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...');

                    $.ajax({
                        url: '{{ route('leave-requests.approve', $leave_request->id) }}',
                        method: 'POST',
                        data: {
                            approver_signature: signatureData
                        },
                        success: function(res) {
                            if (res.success) {
                                toastr.success(res.message);
                                location.reload();
                            } else {
                                toastr.error(res.message);
                                btn.prop('disabled', false).html(
                                    '<i class="fas fa-check me-1"></i> อนุมัติ');
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'เกิดข้อผิดพลาด');
                            btn.prop('disabled', false).html(
                                '<i class="fas fa-check me-1"></i> อนุมัติ');
                        }
                    });
                });

                $('#rejectBtn').on('click', function() {
                    var reason = $('#rejectionReason').val();
                    if (!reason) {
                        alert('กรุณาระบุเหตุผลไม่อนุมัติ');
                        return;
                    }

                    var btn = $(this);
                    btn.prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...');

                    $.ajax({
                        url: '{{ route('leave-requests.reject', $leave_request->id) }}',
                        method: 'POST',
                        data: {
                            rejection_reason: reason
                        },
                        success: function(res) {
                            if (res.success) {
                                toastr.success(res.message);
                                location.reload();
                            } else {
                                toastr.error(res.message);
                                btn.prop('disabled', false).html(
                                    '<i class="fas fa-times me-1"></i> ไม่อนุมัติ');
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'เกิดข้อผิดพลาด');
                            btn.prop('disabled', false).html(
                                '<i class="fas fa-times me-1"></i> ไม่อนุมัติ');
                        }
                    });
                });
            @endif
        });
    </script>
@endsection
