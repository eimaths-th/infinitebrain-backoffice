@extends('layouts.main_template')

@section('style')
<style>
    .permission-group {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .permission-group-title {
        font-weight: 600;
        color: #495057;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 15px;
        text-transform: uppercase;
    }
    .permission-item {
        padding: 5px 0;
    }
    .select-all-btn {
        font-size: 0.8rem;
    }
</style>
@endsection

@section('content')
<div class="row">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-pen icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    แก้ไข Role: {{ $role->name }}
                    <div class="page-title-subheading">แก้ไขชื่อและสิทธิ์สำหรับ Role</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="main-card mb-3 card">
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">ชื่อ Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', $role->name) }}" 
                                   placeholder="เช่น finance, reviewer, approver" 
                                   {{ $role->name === 'super-admin' ? 'readonly' : '' }}
                                   required>
                            @if($role->name === 'super-admin')
                            <small class="text-warning">ไม่สามารถเปลี่ยนชื่อ super-admin ได้</small>
                            @endif
                        </div>
                    </div>
                </div>

                <h5 class="mb-3"><i class="fa fa-lock"></i> กำหนด Permissions</h5>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPermissions()">
                        <i class="fa fa-check-square-o"></i> เลือกทั้งหมด
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllPermissions()">
                        <i class="fa fa-square-o"></i> ยกเลิกทั้งหมด
                    </button>
                </div>

                @if(count($groupedPermissions) > 0)
                <div class="row">
                    @foreach($groupedPermissions as $module => $perms)
                    <div class="col-md-6 col-lg-4">
                        <div class="permission-group">
                            <div class="permission-group-title d-flex justify-content-between">
                                <span><i class="fa fa-folder-o"></i> {{ ucfirst($module) }}</span>
                                <button type="button" class="btn btn-link btn-sm select-all-btn p-0" 
                                        onclick="toggleModulePermissions('{{ $module }}')">
                                    เลือกทั้งหมด
                                </button>
                            </div>
                            @foreach($perms as $permission)
                            <div class="permission-item">
                                <div class="form-check">
                                    <input class="form-check-input permission-checkbox module-{{ $module }}" 
                                           type="checkbox" name="permissions[]" 
                                           value="{{ $permission->name }}" 
                                           id="perm_{{ $permission->id }}"
                                           {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> ยังไม่มี Permission ในระบบ 
                    <a href="{{ route('permissions.create') }}">สร้าง Permission</a> ก่อน
                </div>
                @endif

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> บันทึก
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fa fa-times"></i> ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function selectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
}

function toggleModulePermissions(module) {
    const checkboxes = document.querySelectorAll('.module-' + module);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}
</script>
@endsection
