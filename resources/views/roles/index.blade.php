@extends('layouts.main_template')

@section('style')
<!-- Helper classes for role badges -->
<style>
    .badge-role-super-admin { background-color: #d92550; color: #fff; }
    .badge-role-admin { background-color: #f7b924; color: #fff; }
    .badge-role-staff { background-color: #3f6ad8; color: #fff; }
    .badge-role-finance { background-color: #3ac47d; color: #fff; }
    .badge-role-parent { background-color: #16aaff; color: #fff; }
    .badge-role-default { background-color: #6c757d; color: #fff; }
    
    .action-icon {
        font-size: 0.9rem;
        margin-right: 5px;
    }
</style>
@endsection

@section('content')
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-shield icon-gradient bg-happy-itmeo"></i>
                </div>
                <div>
                    จัดการบทบาทผู้ใช้งาน (Roles)
                    <div class="page-title-subheading">
                        กำหนดสิทธิ์การเข้าถึงและการใช้งานระบบตามตำแหน่งหรือหน้าที่
                    </div>
                </div>
            </div>
            <div class="page-title-actions">
                @can('role.create')
                <a href="{{ route('admin.roles.create') }}" class="btn-shadow btn btn-primary">
                    <i class="fa fa-plus"></i> เพิ่มบทบาทใหม่
                </a>
                @endcan
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="main-card mb-3 card">
        <div class="card-body">
            <h5 class="card-title">รายการบทบาททั้งหมด</h5>
            <div class="table-responsive">
                <table id="rolesTable" class="align-middle mb-0 table table-borderless table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%">#</th>
                            <th style="width: 20%">ชื่อบทบาท (Role)</th>
                            <th class="text-center" style="width: 15%">ผู้ใช้งาน (คน)</th>
                            <th style="width: 40%">สิทธิ์การใช้งาน (Permissions)</th>
                            <th class="text-center" style="width: 20%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $key => $role)
                        <tr>
                            <td class="text-center text-muted">{{ $key + 1 }}</td>
                            <td>
                                @php
                                    $badgeClass = 'badge-role-default';
                                    if(str_contains($role->name, 'super-admin')) $badgeClass = 'badge-role-super-admin';
                                    elseif(str_contains($role->name, 'admin')) $badgeClass = 'badge-role-admin';
                                    elseif(str_contains($role->name, 'staff')) $badgeClass = 'badge-role-staff';
                                    elseif(str_contains($role->name, 'finance')) $badgeClass = 'badge-role-finance';
                                    elseif(str_contains($role->name, 'parent')) $badgeClass = 'badge-role-parent';
                                @endphp
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left mr-3">
                                            <div class="widget-content-left">
                                                <div class="badge {{ $badgeClass }} badge-pill p-2" style="min-width: 80px;">
                                                    {{ $role->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-1 small text-muted">
                                    <i class="fa fa-clock-o"></i> {{ $role->created_at->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    <i class="fa fa-users text-primary"></i> {{ $role->users_count }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($role->permissions->take(4) as $perm)
                                        <span class="badge bg-secondary opacity-75">{{ $perm->name }}</span>
                                    @empty
                                        <span class="text-muted small">- ไม่มีสิทธิ์ -</span>
                                    @endforelse
                                    
                                    @if($role->permissions->count() > 4)
                                        <span class="badge bg-light text-dark border">
                                            +{{ $role->permissions->count() - 4 }} อื่นๆ
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">รวม {{ $role->permissions->count() }} สิทธิ์</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @can('role.view')
                                    <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-sm btn-info text-white" data-bs-toggle="tooltip" title="ดูรายละเอียด">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('role.edit')
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning text-white" data-bs-toggle="tooltip" title="แก้ไข">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endcan

                                    @if($role->name !== 'super-admin')
                                        @can('role.delete')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="tooltip" title="ลบ">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled data-bs-toggle="tooltip" title="ไม่สามารถลบ Super Admin ได้"><i class="fa fa-lock"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#rolesTable').DataTable({
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                "zeroRecords": "ไม่พบข้อมูล",
                "info": "หน้า _PAGE_ จาก _PAGES_",
                "infoEmpty": "ไม่มีข้อมูล",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "search": "ค้นหา:",
                "paginate": {
                    "first": "หน้าแรก",
                    "last": "หน้าสุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": 4 } // Disable sorting on Actions column
            ]
        });

        // Delete Confirmation
        $('body').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "หากลบแล้วจะไม่สามารถกู้คืนได้ และผู้ใช้งานในบทบาทนี้จะสูญเสียสิทธิ์!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ยืนยัน ลบข้อมูล',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection
