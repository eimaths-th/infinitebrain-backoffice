@extends('layouts.main_template')

@section('style')
    <style>
        .branch-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 12px;
        }
        .branch-table td {
            vertical-align: middle;
            padding: 15px 12px;
        }
        .branch-code {
            font-weight: 700;
            color: #2d3748;
            background: #e2e8f0;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .prefix-badge {
            font-family: inherit;
            font-weight: 600;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 4px;
        }
        .bg-rv { background: #e3f2fd; color: #1976d2; }
        .bg-pv { background: #fff3e0; color: #f57c00; }
        .bg-bf { background: #f3e5f5; color: #7b1fa2; }
        .btn-action-group .btn {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin: 0 2px;
        }
        .btn-edit { background: #fff3e0; color: #f57c00; border: none; }
        .btn-edit:hover { background: #f57c00; color: white; }
        .btn-delete { background: #ffebee; color: #d32f2f; border: none; }
        .btn-delete:hover { background: #d32f2f; color: white; }
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

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-map-marker icon-gradient bg-mixed-hopes"></i>
                    </div>
                    <div>
                        <span class="fw-bold">จัดการสาขา (Branches)</span>
                        <div class="page-title-subheading">จัดการสาขาย่อยและรหัสเอกสาร (Prefix) ของแต่ละสาขา</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('admin.branch-groups.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fa fa-network-wired"></i> จัดการกลุ่มสาขา
                    </a>
                    <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus-circle"></i> เพิ่มสาขาใหม่
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="main-card mb-3 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">รายการสาขาทั้งหมด</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table branch-table mb-0">
                            <thead>
                                <tr>
                                    <th>รหัสสาขา</th>
                                    <th>ชื่อสาขา</th>
                                    <th>กลุ่มสาขา</th>
                                    <th>Prefix (RV / PV / Bank Fee)</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-center">การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td><span class="branch-code">{{ $branch->code }}</span></td>
                                        <td><span class="fw-bold text-dark">{{ $branch->name }}</span></td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $branch->group->name ?? 'ไม่ระบุ' }}</span>
                                        </td>
                                        <td>
                                            <span class="prefix-badge bg-rv" title="Receipt Voucher">RV: {{ $branch->rv_prefix ?? '-' }}</span>
                                            <span class="prefix-badge bg-pv" title="Payment Voucher">PV: {{ $branch->pv_prefix ?? '-' }}</span>
                                            <span class="prefix-badge bg-bf" title="Bank Fee">BF: {{ $branch->bf_prefix ?? '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($branch->is_active)
                                                <span class="badge bg-success">เปิดใช้งาน</span>
                                            @else
                                                <span class="badge bg-danger">ปิดใช้งาน</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-action-group">
                                                <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn btn-edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบสาขา {{ $branch->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
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
    </div>
@endsection
