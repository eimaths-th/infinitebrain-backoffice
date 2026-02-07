@extends('layouts.main_template')

@section('style')
    <style>
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card .stat-number {
            font-size: 28px;
            font-weight: 700;
        }

        .role-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 12px;
        }

        .role-table td {
            vertical-align: middle;
            padding: 15px 12px;
        }

        .role-table tbody tr {
            transition: all 0.2s ease;
        }

        .role-table tbody tr:hover {
            background-color: #f8f9ff;
        }

        .role-name {
            font-weight: 600;
            color: #2d3748;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .role-super-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .role-admin {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .role-finance {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .role-staff {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .role-default {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }

        .permission-chip {
            display: inline-block;
            background: #e8f4fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            margin: 2px;
            font-weight: 500;
        }

        .permission-more {
            background: #667eea;
            color: white;
        }

        .user-count-badge {
            background: #e3f2fd;
            color: #1565c0;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-action-group .btn {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin: 0 2px;
        }

        .btn-view {
            background: #e3f2fd;
            color: #1976d2;
            border: none;
        }

        .btn-view:hover {
            background: #1976d2;
            color: white;
        }

        .btn-edit {
            background: #fff3e0;
            color: #f57c00;
            border: none;
        }

        .btn-edit:hover {
            background: #f57c00;
            color: white;
        }

        .btn-delete {
            background: #ffebee;
            color: #d32f2f;
            border: none;
        }

        .btn-delete:hover {
            background: #d32f2f;
            color: white;
        }

        .search-box {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 80px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        @if ($message = Session::get('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-2"></i>{{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        @if ($message = Session::get('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-circle me-2"></i>{{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <!-- Page Header -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-id icon-gradient bg-mixed-hopes"></i>
                    </div>
                    <div>
                        <span class="fw-bold">จัดการบทบาท (Roles)</span>
                        <div class="page-title-subheading">กำหนดบทบาทและสิทธิ์การเข้าถึงของผู้ใช้ในระบบ</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fa fa-key"></i> จัดการ Permissions
                    </a>
                    <a href="{{ route('admin.user-roles.index') }}" class="btn btn-outline-success me-2">
                        <i class="fa fa-users"></i> กำหนด Role ผู้ใช้
                    </a>
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus-circle"></i> เพิ่ม Role ใหม่
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fa fa-shield"></i>
                            </div>
                            <div>
                                <div class="stat-number text-primary">{{ $roles->count() }}</div>
                                <div class="text-muted">Total Roles</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="fa fa-users"></i>
                            </div>
                            <div>
                                <div class="stat-number text-success">{{ $roles->sum(fn($r) => $r->users->count()) }}</div>
                                <div class="text-muted">Users with Roles</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="fa fa-key"></i>
                            </div>
                            <div>
                                <div class="stat-number text-info">{{ \Spatie\Permission\Models\Permission::count() }}</div>
                                <div class="text-muted">Total Permissions</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="fa fa-user-shield"></i>
                            </div>
                            <div>
                                <div class="stat-number text-warning">
                                    {{ $roles->where('name', 'super-admin')->first()?->users->count() ?? 0 }}</div>
                                <div class="text-muted">Super Admins</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="col-12">
            <div class="main-card mb-3 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-list me-2"></i>รายการ Roles ทั้งหมด</h5>
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="searchRole" placeholder="ค้นหา role...">
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($roles->count() > 0)
                        <div class="table-responsive">
                            <table class="table role-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 20%">Role Name</th>
                                        <th style="width: 10%">Level</th>
                                        <th style="width: 12%">ผู้ใช้</th>
                                        <th style="width: 35%">Permissions</th>
                                        <th style="width: 12%">Guard</th>
                                        <th style="width: 16%" class="text-center">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody id="roleTableBody">
                                    @foreach ($roles as $index => $role)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match ($role->name) {
                                                        'super-admin' => 'role-super-admin',
                                                        'admin' => 'role-admin',
                                                        'finance' => 'role-finance',
                                                        'staff' => 'role-staff',
                                                        default => 'role-default',
                                                    };
                                                    $icon = match ($role->name) {
                                                        'super-admin' => 'fa-crown',
                                                        'admin' => 'fa-user-tie',
                                                        'finance' => 'fa-wallet',
                                                        'staff' => 'fa-user',
                                                        'parent' => 'fa-user-friends',
                                                        default => 'fa-tag',
                                                    };
                                                @endphp
                                                <span class="role-badge {{ $badgeClass }}">
                                                    <i class="fa {{ $icon }} me-1"></i>{{ $role->name }}
                                                </span>
                                            </td>
                                            <td> {{ $role->level }} </td>
                                            <td>
                                                <span class="user-count-badge">
                                                    <i class="fa fa-users me-1"></i>{{ $role->users->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                @forelse($role->permissions->take(4) as $permission)
                                                    <span class="permission-chip">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="text-muted fst-italic">ไม่มี permission</span>
                                                @endforelse
                                                @if ($role->permissions->count() > 4)
                                                    <span class="permission-chip permission-more">
                                                        +{{ $role->permissions->count() - 4 }} more
                                                    </span>
                                                @endif
                                            </td>
                                            <td><code>{{ $role->guard_name }}</code></td>
                                            <td>
                                                <div class="btn-action-group text-center text-nowrap">
                                                    <a href="{{ route('admin.roles.show', $role->id) }}"
                                                        class="btn btn-view" title="ดูรายละเอียด">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                                        class="btn btn-edit" title="แก้ไข">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    @if (!in_array($role->name, ['super-admin']))
                                                        <form action="{{ route('admin.roles.destroy', $role->id) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('ยืนยันการลบ Role: {{ $role->name }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-delete" title="ลบ">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-folder-open"></i>
                            <h5 class="text-muted">ยังไม่มี Role ในระบบ</h5>
                            <p class="text-muted">เริ่มต้นด้วยการสร้าง Role แรกของคุณ</p>
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa fa-plus-circle me-2"></i>สร้าง Role แรก
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#searchRole').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#roleTableBody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
@endsection
