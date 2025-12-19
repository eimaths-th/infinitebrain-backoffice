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
    .current-signature {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
    }
    .current-signature img {
        max-height: 150px;
        max-width: 100%;
    }
</style>
@endsection

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-signature icon-gradient bg-tempting-azure"></i>
            </div>
            <div>จัดการลายเซ็น
                <div class="page-title-subheading">อัพโหลดหรือเซ็นลายเซ็นของคุณเพื่อใช้ในเอกสาร</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="main-card card">
            <div class="card-header">
                <i class="fas fa-user me-2"></i> ข้อมูลติดต่อ
            </div>
            <div class="card-body">
                <form action="{{ route('profile.contact.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">เบอร์มือถือ</label>
                                <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="0812345678">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">ที่อยู่ (สำหรับติดต่อกลับ)</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="บ้านเลขที่ ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์">{{ $user->address }}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> บันทึกข้อมูลติดต่อ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="fas fa-image me-2"></i> ลายเซ็นปัจจุบัน
            </div>
            <div class="card-body">
                <div class="current-signature">
                    @if($user->signature)
                        @if($user->signature_is_image)
                        <img src="{{ Storage::url($user->signature) }}" alt="ลายเซ็น" id="currentSignatureImg">
                        @else
                        <img src="{{ $user->signature }}" alt="ลายเซ็น" id="currentSignatureImg">
                        @endif
                        <p class="text-success mt-2 mb-0"><i class="fas fa-check-circle me-1"></i> มีลายเซ็นแล้ว</p>
                    @else
                        <i class="fas fa-signature fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">ยังไม่มีลายเซ็น</p>
                    @endif
                </div>

                @if($user->signature)
                <div class="mt-3 text-center">
                    <form action="{{ route('profile.signature.delete') }}" method="POST" class="d-inline"
                          onsubmit="return confirm('ต้องการลบลายเซ็นหรือไม่?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i> ลบลายเซ็น
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i> อัพเดทลายเซ็น
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" id="signatureTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#uploadTab" type="button">
                            <i class="fas fa-upload me-1"></i> อัพโหลดรูป
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#drawTab" type="button">
                            <i class="fas fa-pen me-1"></i> เซ็นด้วยมือ
                        </button>
                    </li>
                </ul>

                <form id="signatureForm" action="{{ route('profile.signature.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="uploadTab">
                            <div class="mb-3">
                                <label class="form-label">เลือกไฟล์รูปลายเซ็น</label>
                                <input type="file" name="signature_file" class="form-control" accept="image/*" id="signatureFileInput">
                                <small class="text-muted">รองรับไฟล์: JPG, PNG, GIF, WEBP (ขนาดไม่เกิน 2MB)</small>
                            </div>
                            <div id="uploadPreview" class="text-center mb-3" style="display: none;">
                                <img id="uploadPreviewImg" style="max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                        </div>

                        <div class="tab-pane fade" id="drawTab">
                            <div class="signature-pad-container mb-2">
                                <canvas id="signaturePad"></canvas>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="clearSignature">
                                <i class="fas fa-eraser me-1"></i> ล้างลายเซ็น
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="signature_data" id="signatureData">
                    <input type="hidden" name="signature_type" id="signatureType" value="upload">

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> บันทึกลายเซ็น
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
$(document).ready(function() {
    var canvas = document.getElementById('signaturePad');
    var signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
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

    $('#signatureFileInput').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('ไฟล์มีขนาดเกิน 2MB');
                $(this).val('');
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $('#uploadPreviewImg').attr('src', e.target.result);
                $('#uploadPreview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).data('bs-target');
        if (target === '#drawTab') {
            $('#signatureType').val('draw');
            ResizeCanvas();
        } else {
            $('#signatureType').val('upload');
        }
    });

    $('#signatureForm').on('submit', function(e) {
        var signatureType = $('#signatureType').val();

        if (signatureType === 'draw') {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('กรุณาเซ็นลายเซ็น');
                return false;
            }
            $('#signatureData').val(signaturePad.toDataURL());
        } else {
            if (!$('#signatureFileInput').val()) {
                e.preventDefault();
                alert('กรุณาเลือกไฟล์รูปลายเซ็น');
                return false;
            }
        }
    });
});
</script>
@endsection
