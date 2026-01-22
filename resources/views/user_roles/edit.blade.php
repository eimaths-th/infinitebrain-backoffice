@extends('layouts.main_template')

@section('content')
<div class="row">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-id icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    กำหนด Role ให้ผู้ใช้
                    <div class="page-title-subheading">{{ $user->name }} ({{ $user->email }})</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('admin.user-roles.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-user"></i> ข้อมูลผู้ใช้</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">ชื่อ:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>อีเมล:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Level (เดิม):</th>
                            <td>
                                @switch($user->level)
                                    @case(0)
                                        <span class="badge bg-danger">Super Admin (0)</span>
                                        @break
                                    @case(1)
                                        <span class="badge bg-primary">Admin (1)</span>
                                        @break
                                    @case(2)
                                        <span class="badge bg-info">Staff (2)</span>
                                        @break
                                    @case(3)
                                        <span class="badge bg-secondary">Parent (3)</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ $user->level }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <th>Roles ปัจจุบัน:</th>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge bg-success me-1">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">ยังไม่มี Role</span>
                                @endforelse
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-shield"></i> กำหนด Roles</h5>
                </div>
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

                    <form action="{{ route('admin.user-roles.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold">เลือก Roles:</label>
                            <div class="row">
                                @foreach($roles as $role)
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="roles[]" value="{{ $role->name }}" 
                                               id="role_{{ $role->id }}"
                                               {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                            <small class="text-muted d-block">
                                                ({{ $role->permissions->count() }} permissions)
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> บันทึก Roles
                            </button>
                            <a href="{{ route('admin.user-roles.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="fa fa-key"></i> Permissions ที่มี
                </div>
                <div class="card-body">
                    @php
                        $allPermissions = collect();
                        foreach($user->roles as $role) {
                            $allPermissions = $allPermissions->merge($role->permissions);
                        }
                        $allPermissions = $allPermissions->unique('id');
                        
                        $groupedPermissions = $allPermissions->groupBy(function($item) {
                            $parts = explode('.', $item->name);
                            return count($parts) > 1 ? $parts[0] : 'general';
                        });
                    @endphp

                    @if($allPermissions->count() > 0)
                        @foreach($groupedPermissions as $module => $perms)
                        <div class="mb-2">
                            <strong class="text-uppercase small">{{ $module }}</strong>
                            <div>
                                @foreach($perms as $perm)
                                    <span class="badge bg-light text-dark me-1 mb-1">{{ $perm->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">ยังไม่มี permissions</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <i class="fa fa-info-circle"></i> หมายเหตุ
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>User สามารถมีหลาย Roles ได้</li>
                        <li>Permissions จะรวมจากทุก Roles ที่ User มี</li>
                        <li>Role <strong>super-admin</strong> มีสิทธิ์ทุกอย่างในระบบ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
