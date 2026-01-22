@extends('layouts.main_template')

@section('style')
<style>
    .permission-module {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .permission-module-title {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .permission-item {
        background: white;
        padding: 8px 12px;
        border-radius: 5px;
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection

@section('content')
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

    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-lock icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    จัดการ Permissions
                    <div class="page-title-subheading">กำหนดสิทธิ์การเข้าถึงฟังก์ชันต่างๆ ในระบบ</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> เพิ่ม Permission
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                    <i class="fa fa-magic"></i> สร้างแบบ Bulk
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-primary fs-6">ทั้งหมด {{ $permissions->count() }} permissions</span>
                    </div>

                    @if(count($groupedPermissions) > 0)
                    <div class="row">
                        @foreach($groupedPermissions as $module => $perms)
                        <div class="col-md-6 col-lg-4">
                            <div class="permission-module">
                                <div class="permission-module-title">
                                    <i class="fa fa-folder-o"></i> {{ $module }}
                                    <span class="badge bg-secondary">{{ count($perms) }}</span>
                                </div>
                                @foreach($perms as $permission)
                                <div class="permission-item">
                                    <span>{{ $permission->name }}</span>
                                    <div>
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-link text-warning p-0 me-2">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirm('ยืนยันการลบ Permission นี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> ยังไม่มี Permission ในระบบ 
                        <a href="{{ route('admin.permissions.create') }}" class="alert-link">สร้าง Permission แรก</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Create Modal -->
<div class="modal fade" id="bulkCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.permissions.bulk-create') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-magic"></i> สร้าง Permissions แบบ Bulk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อ Module <span class="text-danger">*</span></label>
                        <input type="text" name="module" class="form-control" placeholder="เช่น payment-voucher, students" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Actions <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="view" id="action_view" checked>
                                    <label class="form-check-label" for="action_view">view (ดู)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="create" id="action_create" checked>
                                    <label class="form-check-label" for="action_create">create (สร้าง)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="edit" id="action_edit" checked>
                                    <label class="form-check-label" for="action_edit">edit (แก้ไข)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="delete" id="action_delete" checked>
                                    <label class="form-check-label" for="action_delete">delete (ลบ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="approve" id="action_approve">
                                    <label class="form-check-label" for="action_approve">approve (อนุมัติ)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actions[]" value="export" id="action_export">
                                    <label class="form-check-label" for="action_export">export (ส่งออก)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fa fa-info-circle"></i> จะสร้าง permissions ในรูปแบบ: <code>module.action</code><br>
                        เช่น: payment-voucher.view, payment-voucher.create
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">สร้าง Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
