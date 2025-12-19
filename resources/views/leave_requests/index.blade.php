@extends('layouts.main_template')

@section('style')
<style>
    .badge-leave-type {
        font-size: 0.85em;
    }
</style>
@endsection

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-file-alt icon-gradient bg-tempting-azure"></i>
            </div>
            <div>ใบลา
                <div class="page-title-subheading">รายการใบลาทั้งหมด</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('leave-requests.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> เขียนใบลาใหม่
            </a>
            @if(Auth::user()->level <= 1)
            <a href="{{ route('leave-requests.pending') }}" class="btn btn-warning">
                <i class="fas fa-clock me-1"></i> รออนุมัติ
            </a>
            @endif
        </div>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> ตัวกรอง
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('leave-requests.index') }}">
            <div class="row g-3">
                @if(Auth::user()->level <= 1)
                <div class="col-md-3">
                    <label class="form-label">พนักงาน</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">ประเภทการลา</label>
                    <select name="leave_type" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($leave_types as $key => $name)
                        <option value="{{ $key }}" {{ request('leave_type') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($statuses as $key => $name)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> ล้าง
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>พนักงาน</th>
                        <th>ประเภท</th>
                        <th>วันที่ลา</th>
                        <th>จำนวนวัน</th>
                        <th>สถานะ</th>
                        <th>วันที่ยื่น</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leave_requests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>{{ $request->user->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info badge-leave-type">
                                {{ $request->GetLeaveTypeName() }}
                            </span>
                        </td>
                        <td>{{ $request->GetDateRangeText() }}</td>
                        <td>{{ $request->total_days }} วัน</td>
                        <td>
                            <span class="badge {{ $request->GetStatusBadgeClass() }}">
                                {{ $request->GetStatusName() }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('leave-requests.show', $request->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($request->IsPending() && ($request->user_id == Auth::id() || Auth::user()->level <= 1))
                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $request->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">ไม่มีข้อมูลใบลา</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $leave_requests->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('.btn-delete').on('click', function() {
        if (!confirm('ต้องการลบใบลานี้?')) return;

        var id = $(this).data('id');
        var row = $(this).closest('tr');

        $.ajax({
            url: '/leave-requests/' + id,
            method: 'DELETE',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    row.fadeOut();
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'เกิดข้อผิดพลาด');
            }
        });
    });
});
</script>
@endsection
