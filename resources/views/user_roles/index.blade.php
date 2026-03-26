@extends('layouts.main_template')

@section('style')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .role-badge {
        font-size: 0.85em;
        margin-right: 3px;
        margin-bottom: 3px;
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

    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-users icon-gradient bg-tempting-azure"></i>
                </div>
                <div>
                    จัดการ User Roles
                    <div class="page-title-subheading">กำหนด Role ให้กับผู้ใช้ในระบบ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">กรอง Role</label>
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ค้นหา</label>
                    <input type="text" name="search" class="form-control" placeholder="ชื่อ, อีเมล" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> ค้นหา
                    </button>
                    <a href="{{ route('admin.user-roles.index') }}" class="btn btn-secondary">
                        <i class="fa fa-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover" id="usersTable">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>ชื่อ</th>
                                <th>อีเมล</th>
                                <th>Roles</th>
                                <th width="100">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-success role-badge">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">ยังไม่มี Role</span>
                                    @endforelse
                                </td>
                                <td>
                                    <a href="{{ route('admin.user-roles.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-edit"></i> กำหนด Role
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Optional: Add any additional functionality here
</script>
@endsection
