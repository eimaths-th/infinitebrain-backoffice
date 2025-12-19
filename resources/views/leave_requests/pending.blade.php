@extends('layouts.main_template')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-clock icon-gradient bg-warning"></i>
            </div>
            <div>ใบลารออนุมัติ
                <div class="page-title-subheading">รายการใบลาที่รอการพิจารณา</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-list me-1"></i> ดูทั้งหมด
            </a>
        </div>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-body">
        @if($pending_requests->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>พนักงาน</th>
                        <th>ประเภท</th>
                        <th>วันที่ลา</th>
                        <th>จำนวนวัน</th>
                        <th>เหตุผล</th>
                        <th>วันที่ยื่น</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pending_requests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>
                            <strong>{{ $request->user->name ?? '-' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ $request->GetLeaveTypeName() }}
                            </span>
                        </td>
                        <td>{{ $request->GetDateRangeText() }}</td>
                        <td>{{ $request->total_days }} วัน</td>
                        <td>
                            <span title="{{ $request->reason }}">
                                {{ Str::limit($request->reason, 50) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('leave-requests.show', $request->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i> พิจารณา
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $pending_requests->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            <h4 class="mt-3">ไม่มีใบลารออนุมัติ</h4>
            <p class="text-muted">ใบลาทั้งหมดได้รับการพิจารณาแล้ว</p>
        </div>
        @endif
    </div>
</div>
@endsection
