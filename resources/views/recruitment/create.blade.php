@extends('layouts.main_template')

@section('content')
    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-add-user icon-gradient bg-plum-plate"></i>
                    </div>
                    <div>
                        เพิ่มผู้สมัครใหม่
                        <div class="page-title-subheading">กรอกข้อมูลผู้สมัครและอัปโหลด Resume (PDF เท่านั้น)</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('recruitment.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับไปรายการ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8 offset-md-2">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">ข้อมูลผู้สมัคร</h5>
                    <form action="{{ route('recruitment.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ตำแหน่งที่สมัคร</label>
                            <input type="text" name="position_applied" class="form-control"
                                value="{{ old('position_applied') }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">เอกสาร Resume และเอกสารอื่นๆ (PDF เท่านั้น) <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="resumes[]" class="form-control" accept=".pdf" multiple required>
                            <div class="form-text text-muted">สามารถเลือกได้หลายไฟล์, ขนาดแต่ละไฟล์ไม่เกิน 10MB</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-upload"></i> อัปโหลดและบันทึก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
