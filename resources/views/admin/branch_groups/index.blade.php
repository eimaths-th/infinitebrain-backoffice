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
        .group-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 12px;
        }
        .group-table td {
            vertical-align: middle;
            padding: 15px 12px;
        }
        .group-code {
            font-weight: 700;
            color: #4a5568;
            background: #edf2f7;
            padding: 4px 10px;
            border-radius: 6px;
        }
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
        @if ($message = Session::get('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-circle me-2"></i>{{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-network icon-gradient bg-mixed-hopes"></i>
                    </div>
                    <div>
                        <span class="fw-bold">จัดการกลุ่มสาขา (Branch Groups)</span>
                        <div class="page-title-subheading">จัดการกลุ่มหลักของสถาบัน เช่น Eye Level, Infinite Brain</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fa fa-map-marker-alt"></i> จัดการสาขาย่อย
                    </a>
                    <a href="{{ route('admin.branch-groups.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus-circle"></i> เพิ่มกลุ่มสาขาใหม่
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="main-card mb-3 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">รายการกลุ่มสาขาทั้งหมด</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table group-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 10%">รหัสกลุ่ม</th>
                                    <th style="width: 30%">ชื่อกลุ่ม</th>
                                    <th style="width: 30%">คำอธิบาย</th>
                                    <th style="width: 10%" class="text-center">จำนวนสาขา</th>
                                    <th style="width: 20%" class="text-center">การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $group)
                                    <tr>
                                        <td><span class="group-code">{{ $group->code }}</span></td>
                                        <td><span class="fw-bold text-dark">{{ $group->name }}</span></td>
                                        <td class="text-muted">{{ $group->description ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $group->branches_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-action-group">
                                                <a href="{{ route('admin.branch-groups.edit', $group->id) }}" class="btn btn-edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.branch-groups.destroy', $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบกลุ่มสาขา {{ $group->name }}?');">
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
