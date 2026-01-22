@extends('layouts.main_template')

@section('content')
<div class="row">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-plus icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    เพิ่ม Permission ใหม่
                    <div class="page-title-subheading">สร้างสิทธิ์การเข้าถึงฟังก์ชันใหม่</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">ชื่อ Permission <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name') }}" 
                                   placeholder="เช่น payment-voucher.approve" required>
                            <small class="text-muted">ควรใช้รูปแบบ: module.action เช่น payment-voucher.view, students.edit</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Guard Name</label>
                            <input type="text" name="guard_name" class="form-control" 
                                   value="{{ old('guard_name', 'web') }}" readonly>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> บันทึก
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="fa fa-lightbulb-o"></i> คำแนะนำ
                </div>
                <div class="card-body">
                    <h6>รูปแบบการตั้งชื่อที่แนะนำ:</h6>
                    <ul class="small">
                        <li><code>module.view</code> - ดูข้อมูล</li>
                        <li><code>module.create</code> - สร้างข้อมูล</li>
                        <li><code>module.edit</code> - แก้ไขข้อมูล</li>
                        <li><code>module.delete</code> - ลบข้อมูล</li>
                        <li><code>module.approve</code> - อนุมัติ</li>
                        <li><code>module.export</code> - ส่งออก</li>
                    </ul>
                    <hr>
                    <h6>ตัวอย่าง:</h6>
                    <ul class="small">
                        <li><code>payment-voucher.view</code></li>
                        <li><code>payment-voucher.approve</code></li>
                        <li><code>students.edit</code></li>
                        <li><code>receipts.export</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
