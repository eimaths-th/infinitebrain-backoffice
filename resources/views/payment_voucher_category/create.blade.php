@extends('layouts.main_template')

@section('title', 'เพิ่มหมวดหมู่ใบสำคัญจ่าย')

@section('content')
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-plus icon-gradient bg-mean-fruit"></i>
                </div>
                <div>เพิ่มหมวดหมู่ใบสำคัญจ่าย
                    <div class="page-title-subheading">เพิ่มข้อมูลหมวดหมู่ใหม่</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('payment-voucher-category.index') }}" class="btn-shadow btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> กลับหน้ารายการ
                </a>
            </div>
        </div>
    </div>

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
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>

    <div class="main-card mb-3 card">
        <div class="card-body">
            <form action="{{ route('payment-voucher-category.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="key" class="form-label">รหัสหมวดหมู่ (Key) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="key" name="key" value="{{ old('key') }}" required placeholder="เช่น shop_rent, office_expense">
                        <small class="form-text text-muted">ภาษาอังกฤษตัวพิมพ์เล็กและ underscore เท่านั้น ไม่เว้นวรรค</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="label" class="form-label">ชื่อหมวดหมู่ (Label) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="label" name="label" value="{{ old('label') }}" required placeholder="เช่น ค่าเช่าร้าน, ค่าใช้จ่ายสำนักงาน">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sort_order" class="form-label">ลำดับการแสดงผล</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
            </form>
        </div>
    </div>
</div>
@endsection
