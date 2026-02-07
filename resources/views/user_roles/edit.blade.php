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
                                    @foreach ($roles as $role)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="roles[]"
                                                    value="{{ $role->name }}" id="role_{{ $role->id }}"
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
                <div class="card sticky-top" style="top: 80px; z-index: 1;">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-key"></i> เปรียบเทียบ Permissions</span>
                        <span class="badge bg-light text-dark" id="perm-count-badge">0</span>
                    </div>
                    <div class="card-body p-2" style="max-height: 70vh; overflow-y: auto;">

                        <div id="permission-diff-container">
                            <div class="text-center py-4 text-muted">
                                <i class="fa fa-circle-o-notch fa-spin"></i> กำลังโหลดข้อมูล...
                            </div>
                        </div>

                    </div>
                    <div class="card-footer bg-light p-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-success"><i class="fa fa-plus-circle"></i> เพิ่มใหม่</span>
                            <span class="text-danger"><i class="fa fa-minus-circle"></i> ถูกลบออก</span>
                            <span class="text-secondary"><i class="fa fa-check-circle"></i> คงเดิม</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @php
        $rolesData = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
        });
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data Preparation
            const allRoles = @json($rolesData);

            const initialUserRoles = @json($userRoles);

            // Initial Calculation
            let initialPermissions = new Set();

            // Find permissions from initial roles
            allRoles.forEach(role => {
                if (initialUserRoles.includes(role.name)) {
                    role.permissions.forEach(p => initialPermissions.add(p));
                }
            });

            // DOM Elements
            const checkboxes = document.querySelectorAll('input[name="roles[]"]');
            const container = document.getElementById('permission-diff-container');
            const countBadge = document.getElementById('perm-count-badge');

            function updatePermissions() {
                let currentPermissions = new Set();

                // Calculate currently selected permissions
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        const role = allRoles.find(r => r.name === cb.value);
                        if (role) {
                            role.permissions.forEach(p => currentPermissions.add(p));
                        }
                    }
                });

                // Calculate Diff
                const allPerms = new Set([...initialPermissions, ...currentPermissions]);
                const sortedPerms = Array.from(allPerms).sort();

                // Group by module
                const grouped = {};
                sortedPerms.forEach(perm => {
                    const parts = perm.split('.');
                    const module = parts.length > 1 ? parts[0] : 'general';
                    if (!grouped[module]) grouped[module] = [];

                    let status = 'unchanged';
                    if (currentPermissions.has(perm) && !initialPermissions.has(perm)) status = 'added';
                    else if (!currentPermissions.has(perm) && initialPermissions.has(perm)) status =
                        'removed';

                    grouped[module].push({
                        name: perm,
                        status: status
                    });
                });

                // Render
                if (Object.keys(grouped).length === 0) {
                    container.innerHTML = '<div class="text-center py-4 text-muted">ไม่มี permissions</div>';
                    countBadge.innerText = '0';
                    return;
                }

                let html = '';
                for (const [module, perms] of Object.entries(grouped)) {
                    html += `<div class="mb-3 border-bottom pb-2">
                            <strong class="text-uppercase small text-muted d-block mb-1">${module}</strong>`;

                    perms.forEach(p => {
                        let badgeClass = 'bg-light text-dark';
                        let icon = '';
                        if (p.status === 'added') {
                            badgeClass = 'bg-success text-white';
                            icon = '<i class="fa fa-plus-circle me-1"></i>';
                        } else if (p.status === 'removed') {
                            badgeClass = 'bg-danger text-white text-decoration-line-through opacity-75';
                            icon = '<i class="fa fa-minus-circle me-1"></i>';
                        } else {
                            // Gray out unchanged to make changes pop
                            badgeClass = 'bg-white border text-secondary';
                        }

                        html += `<span class="badge ${badgeClass} me-1 mb-1" style="font-weight: 500; font-size: 0.8rem;">
                                ${icon}${p.name}
                             </span>`;
                    });
                    html += `</div>`;
                }

                container.innerHTML = html;
                countBadge.innerText = currentPermissions.size;
            }

            // Attach listeners
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updatePermissions);
            });

            // Run once on load
            updatePermissions();
        });
    </script>
@endsection
