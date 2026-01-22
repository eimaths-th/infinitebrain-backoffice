@extends('layouts.main_template')

@section('style')
<style>
    .info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    .permission-badge {
        font-size: 0.8rem;
        margin: 3px;
    }
    .user-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .user-item:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-shield icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    Role: {{ $role->name }}
                    <div class="page-title-subheading">รายละเอียดและผู้ใช้ที่มี Role นี้</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning">
                    <i class="fa fa-edit"></i> แก้ไข
                </a>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Role Info -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-info-circle"></i> ข้อมูล Role</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ชื่อ Role:</th>
                            <td><strong>{{ $role->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Guard:</th>
                            <td>{{ $role->guard_name }}</td>
                        </tr>
                        <tr>
                            <th>จำนวน Permission:</th>
                            <td><span class="badge bg-primary">{{ $role->permissions->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>จำนวนผู้ใช้:</th>
                            <td><span class="badge bg-success">{{ $role->users->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>สร้างเมื่อ:</th>
                            <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-lock"></i> Permissions ({{ $role->permissions->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        @php
                            $groupedPerms = $role->permissions->groupBy(function($item) {
                                return explode('.', $item->name)[0];
                            });
                        @endphp
                        @foreach($groupedPerms as $module => $perms)
                        <div class="mb-3">
                            <strong class="text-uppercase text-muted">{{ $module }}</strong>
                            <div class="mt-1">
                                @foreach($perms as $perm)
                                <span class="badge bg-light text-dark permission-badge">{{ $perm->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">ไม่มี Permission</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Users with this Role -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-users"></i> ผู้ใช้ที่มี Role นี้ ({{ $role->users->count() }})</h6>
        </div>
        <div class="card-body">
            @if($role->users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อ</th>
                            <th>Email</th>
                            <th>Roles ทั้งหมด</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($role->users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $r)
                                <span class="badge bg-{{ $r->name === $role->name ? 'primary' : 'secondary' }}">{{ $r->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('user-roles.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-3">ยังไม่มีผู้ใช้ที่มี Role นี้</p>
            @endif
        </div>
    </div>
</div>
@endsection
