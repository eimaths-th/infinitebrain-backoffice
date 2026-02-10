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
                <div>เขียนใบลา
                    <div class="page-title-subheading">กรอกข้อมูลใบลา</div>
                </div>
            </div>
        </div>
    </div>

    <form id="leaveRequestForm" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i> เลือกประเภทการลา
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($leave_types as $key => $name)
                        <div class="col-md-3 col-6">
                            <div class="card leave-type-card text-center p-3" data-type="{{ $key }}">
                                <div class="leave-type-icon">
                                    @switch($key)
                                        @case('sick')
                                            <i class="fas fa-thermometer-half text-danger"></i>
                                        @break

                                        @case('personal')
                                            <i class="fas fa-user-clock text-primary"></i>
                                        @break

                                        @case('maternity')
                                            <i class="fas fa-baby text-pink"></i>
                                        @break

                                        @case('ordination')
                                            <i class="fas fa-pray text-warning"></i>
                                        @break

                                        @case('military')
                                            <i class="fas fa-shield-alt text-success"></i>
                                        @break

                                        @case('vacation')
                                            <i class="fas fa-umbrella-beach text-info"></i>
                                        @break

                                        @case('compensate')
                                            <i class="fas fa-exchange-alt text-secondary"></i>
                                        @break

                                        @case('swap_day')
                                            <i class="fas fa-calendar-alt text-warning"></i>
                                        @break

                                        @case('remote_work')
                                            <i class="fas fa-laptop-house text-success"></i>
                                        @break

                                        @default
                                            <i class="fas fa-file-alt"></i>
                                    @endswitch
                                </div>
                                <div class="fw-bold">{{ $name }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <input type="hidden" name="leave_type" id="leaveType" required>
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
                        <input type="text" name="submitted_date" class="form-control datepicker" required
                            value="{{ old('submitted_date', date('Y-m-d')) }}">
                        <small class="text-muted">สามารถแก้ไขได้หากต้องการยื่นย้อนหลัง</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                        <select name="title_prefix" id="titlePrefix" class="form-select" required>
                            <option value="">-- เลือก --</option>
                            @foreach ($title_prefixes as $key => $name)
                                <option value="{{ $key }}"
                                    {{ old('title_prefix', $user->title_prefix) == $key ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 conditional-field" id="customPrefixField">
                        <label class="form-label">ระบุคำนำหน้า <span class="text-danger">*</span></label>
                        <input type="text" name="custom_title_prefix" id="customTitlePrefix" class="form-control"
                            value="{{ old('custom_title_prefix') }}" placeholder="พิมพ์คำนำหน้า">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ตำแหน่ง <span class="text-danger">*</span></label>
                        <input type="text" name="position" class="form-control" required
                            value="{{ old('position', $user->position) }}" placeholder="ระบุตำแหน่ง">
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
                        <input type="text" name="start_date" class="form-control datepicker" required
                            value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="text" name="end_date" class="form-control datepicker" required
                            value="{{ old('end_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ลาครึ่งวัน</label>
                        <select name="half_day" class="form-select">
                            @foreach ($half_day_options as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 conditional-field" id="timeFields">
                        <label class="form-label">เวลาเริ่ม</label>
                        <input type="time" name="start_time" class="form-control">
                    </div>
                    <div class="col-md-6 conditional-field" id="timeFieldsEnd">
                        <label class="form-label">เวลาสิ้นสุด</label>
                        <input type="time" name="end_time" class="form-control">
                    </div>

                    <div class="col-12 conditional-field" id="locationField">
                        <label class="form-label">สถานที่ปฏิบัติงาน</label>
                        <input type="text" name="location" class="form-control" placeholder="ระบุสถานที่">
                    </div>

                    <div class="col-md-6 conditional-field" id="compensateField">
                        <label class="form-label">ใช้ชดเชยของวันที่</label>
                        <select name="compensate_for_date" class="form-select">
                            <option value="">-- เลือกวันหยุดชดเชย --</option>
                            @foreach ($compensate_days as $day)
                                <option value="{{ $day->holiday_date->format('Y-m-d') }}">
                                    {{ $day->holiday_date->format('d/m/Y') }} - {{ $day->holiday->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 conditional-field" id="swapField">
                        <label class="form-label">สลับจากวันที่</label>
                        <input type="text" name="swap_from_date" class="form-control datepicker">
                    </div>

                    <div class="col-12">
                        <label class="form-label">เหตุผลการลา <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="ระบุเหตุผล">{{ old('reason') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรติดต่อระหว่างลา</label>
                        <input type="text" name="contact_phone" class="form-control" placeholder="0xx-xxx-xxxx"
                            value="{{ old('contact_phone', $user->phone) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ที่อยู่ติดต่อระหว่างลา</label>
                        <input type="text" name="contact_address" class="form-control" placeholder="ที่อยู่"
                            value="{{ old('contact_address', $user->address) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="fas fa-paperclip me-2"></i> เอกสารแนบ (ถ้ามี)
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">แนบไฟล์เอกสารหรือรูปภาพ</label>
                    <input type="file" name="attachments[]" class="form-control" multiple
                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">รองรับไฟล์: รูปภาพ, PDF, Word, Excel (สูงสุด 5 ไฟล์, ไฟล์ละไม่เกิน
                        10MB)</small>
                </div>
                <div id="attachmentPreview" class="row g-2"></div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-signature me-2"></i> ลายเซ็นผู้ยื่นใบลา</span>
                <a href="{{ route('profile.signature') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-cog me-1"></i> จัดการลายเซ็น
                </a>
            </div>
            <div class="card-body">
                @if ($user->signature)
                    <div class="alert alert-success mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                @if ($user->signature_is_image)
                                    <img src="{{ Storage::url($user->signature) }}" alt="ลายเซ็น"
                                        style="max-height: 80px; border: 1px solid #ddd; border-radius: 4px; background: #fff; padding: 5px;">
                                @else
                                    <img src="{{ $user->signature }}" alt="ลายเซ็น"
                                        style="max-height: 80px; border: 1px solid #ddd; border-radius: 4px; background: #fff; padding: 5px;">
                                @endif
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong><i class="fas fa-check-circle me-1"></i> ใช้ลายเซ็นที่บันทึกไว้</strong>
                                <p class="mb-0 small text-muted">ลายเซ็นของคุณจะถูกใช้ในใบลานี้โดยอัตโนมัติ</p>
                            </div>
                        </div>
                        <input type="hidden" id="useSavedSignature" value="saved:{{ $user->signature }}">
                    </div>

                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleNewSignature">
                            <i class="fas fa-pen me-1"></i> เซ็นใหม่แทน
                        </button>
                    </div>

                    <div id="newSignatureSection" style="display: none;">
                    @else
                        <div id="newSignatureSection">
                @endif
                <ul class="nav nav-tabs mb-3" id="signatureTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#drawSignature"
                            type="button">
                            <i class="fas fa-pen me-1"></i> เซ็นด้วยมือ
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadSignature" type="button">
                            <i class="fas fa-upload me-1"></i> อัพโหลดรูปลายเซ็น
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="drawSignature">
                        <div class="signature-pad-container mb-2">
                            <canvas id="signaturePad"></canvas>
                        </div>
                        <button type="button" class="btn btn-secondary" id="clearSignature">
                            <i class="fas fa-eraser me-1"></i> ล้างลายเซ็น
                        </button>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="saveNewSignature"
                                name="save_new_signature" value="1">
                            <label class="form-check-label" for="saveNewSignature">
                                บันทึกลายเซ็นนี้ไว้ใช้ในครั้งต่อไป
                            </label>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="uploadSignature">
                        <input type="file" name="signature_image" class="form-control" accept="image/*"
                            id="signatureImageInput">
                        <div id="signaturePreview" class="mt-2"></div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="saveUploadedSignature"
                                name="save_uploaded_signature" value="1">
                            <label class="form-check-label" for="saveUploadedSignature">
                                บันทึกลายเซ็นนี้ไว้ใช้ในครั้งต่อไป
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="employee_signature" id="employeeSignature" required>
            <input type="hidden" name="use_saved_signature" id="useSavedSignatureFlag"
                value="{{ $user->signature ? '1' : '0' }}">
            @error('employee_signature')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                    </a>
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                        <i class="fas fa-paper-plane me-1"></i> ส่งใบลา
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            var canvas = document.getElementById('signaturePad');
            var signaturePad = new SignaturePad(canvas, {
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
            ResizeCanvas();

            $('#clearSignature').on('click', function() {
                signaturePad.clear();
            });

            $('#titlePrefix').on('change', function() {
                if ($(this).val() === 'other') {
                    $('#customPrefixField').show();
                    $('#customTitlePrefix').prop('required', true);
                } else {
                    $('#customPrefixField').hide();
                    $('#customTitlePrefix').prop('required', false);
                }
            });

            if ($('#titlePrefix').val() === 'other') {
                $('#customPrefixField').show();
            }

            $('.leave-type-card').on('click', function() {
                $('.leave-type-card').removeClass('selected');
                $(this).addClass('selected');
                $('#leaveType').val($(this).data('type'));

                $('.conditional-field:not(#customPrefixField)').hide();

                if ($('#titlePrefix').val() === 'other') {
                    $('#customPrefixField').show();
                }

                var type = $(this).data('type');
                if (type === 'remote_work') {
                    $('#timeFields, #timeFieldsEnd, #locationField').show();
                } else if (type === 'compensate') {
                    $('#compensateField').show();
                } else if (type === 'swap_day') {
                    $('#swapField').show();
                }
            });

            $('#signatureImageInput').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#signaturePreview').html('<img src="' + e.target.result +
                            '" style="max-height: 150px;">');
                        $('#employeeSignature').val(e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).data('bs-target');
                if (target === '#drawSignature') {
                    $('#employeeSignature').val('');
                    ResizeCanvas();
                }
            });

            $('#toggleNewSignature').on('click', function() {
                var section = $('#newSignatureSection');
                if (section.is(':visible')) {
                    section.hide();
                    $(this).html('<i class="fas fa-pen me-1"></i> เซ็นใหม่แทน');
                    $('#useSavedSignatureFlag').val('1');
                } else {
                    section.show();
                    $(this).html('<i class="fas fa-times me-1"></i> ยกเลิก ใช้ลายเซ็นเดิม');
                    $('#useSavedSignatureFlag').val('0');
                    ResizeCanvas();
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
                        '<div class="small text-truncate" style="max-width: 100px;">' + file.name +
                        '</div>' +
                        '</div>' +
                        '</div>'
                    );
                }
            });

            $('#leaveRequestForm').on('submit', function(e) {
                var useSaved = $('#useSavedSignatureFlag').val();

                if (useSaved === '1' && $('#useSavedSignature').length) {
                    $('#employeeSignature').val($('#useSavedSignature').val());
                } else {
                    var activeTab = $('#signatureTab .nav-link.active').data('bs-target');

                    if (activeTab === '#drawSignature') {
                        if (signaturePad.isEmpty()) {
                            e.preventDefault();
                            alert('กรุณาเซ็นชื่อ');
                            return false;
                        }
                        $('#employeeSignature').val(signaturePad.toDataURL());
                    }
                }

                if (!$('#leaveType').val()) {
                    e.preventDefault();
                    alert('กรุณาเลือกประเภทการลา');
                    return false;
                }

                if (!$('#employeeSignature').val()) {
                    e.preventDefault();
                    alert('กรุณาเซ็นชื่อหรือเลือกลายเซ็น');
                    return false;
                }
            });
        });
    </script>
@endsection
