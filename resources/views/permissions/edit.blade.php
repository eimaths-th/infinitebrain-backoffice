@extends('layouts.main_template')

@section('content')
<div class="row">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-pen icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    แก้ไข Permission
                    <div class="page-title-subheading">{{ $permission->name }}</div>
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

                    <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">ชื่อ Permission <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', $permission->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Guard Name</label>
                            <input type="text" name="guard_name" class="form-control" 
                                   value="{{ $permission->guard_name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Roles ที่ใช้ Permission นี้</label>
                            <div>
                                @forelse($permission->roles as $role)
                                    <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">ยังไม่มี Role ที่ใช้ Permission นี้</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="fa fa-exclamation-triangle"></i> Danger Zone
                </div>
                <div class="card-body">
                    <p class="small text-muted">การลบ Permission จะทำให้ทุก Role ที่มีสิทธิ์นี้สูญเสียสิทธิ์ไป</p>
                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST"
                          onsubmit="return confirm('ยืนยันการลบ Permission: {{ $permission->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i> ลบ Permission นี้
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
