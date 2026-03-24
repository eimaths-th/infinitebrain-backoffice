@extends('layouts.main_template')

@section('content')
    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-plus icon-gradient bg-mixed-hopes"></i>
                    </div>
                    <div>
                        <span class="fw-bold">เพิ่มกลุ่มสาขาใหม่</span>
                        <div class="page-title-subheading">สร้างกลุ่มสาขาหลักใหม่ในระบบ</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('admin.branch-groups.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> กลับหน้ารายการ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8 offset-md-2">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">ข้อมูลกลุ่มสาขา</h5>
                    <form action="{{ route('admin.branch-groups.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">รหัสกลุ่มสาขา (Group Code) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="ตัวอย่าง: EL, IF, META" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ใช้เป็นรหัสอ้างอิงภายในระบบ (ต้องไม่ซ้ำกัน)</small>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อกลุ่มสาขา (Group Name) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="ตัวอย่าง: Eye Level, Infinite Brain" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">คำอธิบายเพิ่มเติม</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fa fa-save me-2"></i>บันทึกข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
