@extends('layouts.main_template')

@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.min.css">
<style>
    .signature-pad-container {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
        position: relative;
    }
    #signaturePad {
        width: 100%;
        height: 200px;
    }
    .leave-type-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    .leave-type-card:hover {
        border-color: #4CAF50;
    }
    .leave-type-card.selected {
        border-color: #4CAF50;
        background-color: #e8f5e9;
    }
    .leave-type-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .conditional-field {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-edit icon-gradient bg-tempting-azure"></i>
            </div>
            <div>แก้ไขใบลา #{{ $leave_request->id }}
                <div class="page-title-subheading">{{ $leave_request->GetLeaveTypeName() }}</div>
            </div>
        </div>
    </div>
</div>

<form id="leaveRequestForm" method="POST" action="{{ route('leave-requests.update', $leave_request->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i> เลือกประเภทการลา
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($leave_types as $key => $name)
                <div class="col-md-3 col-6">
                    <div class="card leave-type-card text-center p-3 {{ $leave_request->leave_type == $key ? 'selected' : '' }}" data-type="{{ $key }}">
                        <div class="leave-type-icon">
                            @switch($key)
                                @case('sick') <i class="fas fa-thermometer-half text-danger"></i> @break
                                @case('personal') <i class="fas fa-user-clock text-primary"></i> @break
                                @case('maternity') <i class="fas fa-baby text-pink"></i> @break
                                @case('ordination') <i class="fas fa-pray text-warning"></i> @break
                                @case('military') <i class="fas fa-shield-alt text-success"></i> @break
                                @case('vacation') <i class="fas fa-umbrella-beach text-info"></i> @break
                                @case('compensate') <i class="fas fa-exchange-alt text-secondary"></i> @break
                                @case('swap_day') <i class="fas fa-calendar-alt text-warning"></i> @break
                                @case('remote_work') <i class="fas fa-laptop-house text-success"></i> @break
                                @default <i class="fas fa-file-alt"></i>
                            @endswitch
                        </div>
                        <div class="fw-bold">{{ $name }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <input type="hidden" name="leave_type" id="leaveType" value="{{ $leave_request->leave_type }}" required>
            @error('leave_type')
            <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="fas fa-user me-2"></i> ข้อมูลผู้ยื่นใบลา
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">วันที่ยื่นใบลา <span class="text-danger">*</span></label>
                    <input type="date" name="submitted_date" class="form-control" required
                           value="{{ old('submitted_date', $leave_request->submitted_date ? $leave_request->submitted_date->format('Y-m-d') : date('Y-m-d')) }}">
                    <small class="text-muted">สามารถแก้ไขได้หากต้องการยื่นย้อนหลัง</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">คำนำหน้า</label>
                    <input type="text" class="form-control" value="{{ $user->title_prefix }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ตำแหน่ง</label>
                    <input type="text" class="form-control" value="{{ $user->position }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="fas fa-calendar me-2"></i> ข้อมูลการลา
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">วันที่เริ่มลา <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required
                           value="{{ old('start_date', $leave_request->start_date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" required
                           value="{{ old('end_date', $leave_request->end_date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ลาครึ่งวัน</label>
                    <select name="half_day" class="form-select">
                        @foreach($half_day_options as $key => $name)
                        <option value="{{ $key }}" {{ $leave_request->half_day == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 conditional-field" id="timeFields" style="{{ $leave_request->leave_type == 'remote_work' ? 'display: block;' : '' }}">
                    <label class="form-label">เวลาเริ่ม</label>
                    <input type="time" name="start_time" class="form-control" value="{{ $leave_request->start_time }}">
                </div>
                <div class="col-md-6 conditional-field" id="timeFieldsEnd" style="{{ $leave_request->leave_type == 'remote_work' ? 'display: block;' : '' }}">
                    <label class="form-label">เวลาสิ้นสุด</label>
                    <input type="time" name="end_time" class="form-control" value="{{ $leave_request->end_time }}">
                </div>

                <div class="col-12 conditional-field" id="locationField" style="{{ $leave_request->leave_type == 'remote_work' ? 'display: block;' : '' }}">
                    <label class="form-label">สถานที่ปฏิบัติงาน</label>
                    <input type="text" name="location" class="form-control" placeholder="ระบุสถานที่" value="{{ $leave_request->location }}">
                </div>

                <div class="col-md-6 conditional-field" id="compensateField" style="{{ $leave_request->leave_type == 'compensate' ? 'display: block;' : '' }}">
                    <label class="form-label">ใช้ชดเชยของวันที่</label>
                    <select name="compensate_for_date" class="form-select">
                        <option value="">-- เลือกวันหยุดชดเชย --</option>
                        @foreach($compensate_days as $day)
                        <option value="{{ $day->holiday_date->format('Y-m-d') }}"
                                {{ $leave_request->compensate_for_date && $leave_request->compensate_for_date->format('Y-m-d') == $day->holiday_date->format('Y-m-d') ? 'selected' : '' }}>
                            {{ $day->holiday_date->format('d/m/Y') }} - {{ $day->holiday->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 conditional-field" id="swapField" style="{{ $leave_request->leave_type == 'swap_day' ? 'display: block;' : '' }}">
                    <label class="form-label">สลับจากวันที่</label>
                    <input type="date" name="swap_from_date" class="form-control"
                           value="{{ $leave_request->swap_from_date ? $leave_request->swap_from_date->format('Y-m-d') : '' }}">
                </div>

                <div class="col-12">
                    <label class="form-label">เหตุผลการลา <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="ระบุเหตุผล">{{ old('reason', $leave_request->reason) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">เบอร์โทรติดต่อระหว่างลา</label>
                    <input type="text" name="contact_phone" class="form-control" placeholder="0xx-xxx-xxxx"
                           value="{{ old('contact_phone', $leave_request->contact_phone) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ที่อยู่ติดต่อระหว่างลา</label>
                    <input type="text" name="contact_address" class="form-control" placeholder="ที่อยู่"
                           value="{{ old('contact_address', $leave_request->contact_address) }}">
                </div>
            </div>
        </div>
    </div>

    @if($leave_request->attachments->count() > 0)
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="fas fa-paperclip me-2"></i> เอกสารแนบที่มีอยู่
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($leave_request->attachments as $attachment)
                <div class="col-auto">
                    <div class="border rounded p-2 text-center" style="min-width: 100px;">
                        @if(Str::startsWith($attachment->file_type, 'image/'))
                        <i class="fas fa-file-image fa-2x mb-1 text-primary"></i>
                        @elseif($attachment->file_type == 'application/pdf')
                        <i class="fas fa-file-pdf fa-2x mb-1 text-danger"></i>
                        @else
                        <i class="fas fa-file fa-2x mb-1"></i>
                        @endif
                        <div class="small text-truncate" style="max-width: 100px;">{{ $attachment->file_name }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="fas fa-paperclip me-2"></i> เพิ่มเอกสารแนบ (ถ้ามี)
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">แนบไฟล์เอกสารหรือรูปภาพ</label>
                <input type="file" name="attachments[]" class="form-control" multiple
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                <small class="text-muted">รองรับไฟล์: รูปภาพ, PDF, Word, Excel (สูงสุด 5 ไฟล์, ไฟล์ละไม่เกิน 10MB)</small>
            </div>
            <div id="attachmentPreview" class="row g-2"></div>
        </div>
    </div>

    <div class="main-card mb-3 card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('leave-requests.show', $leave_request->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                </a>
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <i class="fas fa-save me-1"></i> บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.leave-type-card').on('click', function() {
        $('.leave-type-card').removeClass('selected');
        $(this).addClass('selected');
        $('#leaveType').val($(this).data('type'));

        $('.conditional-field').hide();

        var type = $(this).data('type');
        if (type === 'remote_work') {
            $('#timeFields, #timeFieldsEnd, #locationField').show();
        } else if (type === 'compensate') {
            $('#compensateField').show();
        } else if (type === 'swap_day') {
            $('#swapField').show();
        }
    });

    $('input[name="attachments[]"]').on('change', function(e) {
        var files = e.target.files;
        var preview = $('#attachmentPreview');
        preview.empty();

        if (files.length > 5) {
            alert('อัพโหลดได้สูงสุด 5 ไฟล์');
            $(this).val('');
            return;
        }

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (file.size > 10 * 1024 * 1024) {
                alert('ไฟล์ ' + file.name + ' มีขนาดเกิน 10MB');
                continue;
            }

            var icon = 'fa-file';
            if (file.type.startsWith('image/')) icon = 'fa-file-image text-primary';
            else if (file.type === 'application/pdf') icon = 'fa-file-pdf text-danger';
            else if (file.name.match(/\.docx?$/i)) icon = 'fa-file-word text-info';
            else if (file.name.match(/\.xlsx?$/i)) icon = 'fa-file-excel text-success';

            preview.append(
                '<div class="col-auto">' +
                    '<div class="border rounded p-2 text-center" style="min-width: 100px;">' +
                        '<i class="fas ' + icon + ' fa-2x mb-1"></i>' +
                        '<div class="small text-truncate" style="max-width: 100px;">' + file.name + '</div>' +
                    '</div>' +
                '</div>'
            );
        }
    });

    $('#leaveRequestForm').on('submit', function(e) {
        if (!$('#leaveType').val()) {
            e.preventDefault();
            alert('กรุณาเลือกประเภทการลา');
            return false;
        }
    });
});
</script>
@endsection
