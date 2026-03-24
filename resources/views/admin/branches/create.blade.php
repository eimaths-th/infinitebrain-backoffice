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
                        <span class="fw-bold">เพิ่มสาขาใหม่</span>
                        <div class="page-title-subheading">สร้างสาขาย่อยและกำหนดรหัสเอกสารเริ่มต้น</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> กลับหน้ารายการ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-10 offset-md-1">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">ข้อมูลสาขา</h5>
                    <form action="{{ route('admin.branches.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="group_id" class="form-label">กลุ่มสาขา (Branch Group) <span class="text-danger">*</span></label>
                                <select class="form-control @error('group_id') is-invalid @enderror" id="group_id" name="group_id" required>
                                    <option value="">-- เลือกกลุ่มสาขา --</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">รหัสสาขา (Branch Code) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="ตัวอย่าง: EiRP, EiBK, IF" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">ชื่อสาขา (Branch Name) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="ตัวอย่าง: eiMaths RP, Infinite Brain (Main)" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-3 mb-2">
                                <h6 class="fw-bold border-bottom pb-2">รหัสเอกสาร (Voucher Prefix)</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="rv_prefix" class="form-label text-primary fw-bold">Receipt Prefix (RV)</label>
                                <input type="text" class="form-control" id="rv_prefix" name="rv_prefix" value="{{ old('rv_prefix') }}" placeholder="เช่น ELRV, IFRV">
                                <small class="text-muted">ใช้สำหรับใบสำคัญรับ</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="pv_prefix" class="form-label text-warning fw-bold">Payment Prefix (PV)</label>
                                <input type="text" class="form-control" id="pv_prefix" name="pv_prefix" value="{{ old('pv_prefix') }}" placeholder="เช่น ELPV, IFPV">
                                <small class="text-muted">ใช้สำหรับใบสำคัญจ่าย</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="bf_prefix" class="form-label text-purple fw-bold" style="color: #7b1fa2;">Bank Fee Prefix (BF)</label>
                                <input type="text" class="form-control" id="bf_prefix" name="bf_prefix" value="{{ old('bf_prefix') }}" placeholder="เช่น ELBF, IFBF">
                                <small class="text-muted">ใช้สำหรับค่าธรรมเนียมธนาคาร</small>
                            </div>

                            <div class="col-md-6 mb-3 mt-2">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">สถานะเปิดใช้งาน</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fa fa-save me-2"></i>บันทึกข้อมูลสาขา
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
