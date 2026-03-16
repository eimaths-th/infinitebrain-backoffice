@extends('layouts.main_template')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-verified { background-color: #007bff; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .btn-action { padding: 3px 8px; font-size: 12px; margin: 1px; }
        .filter-section { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
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
        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <p class="mb-0">{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-note2 icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        ใบสำคัญรับ (Receipt Voucher)
                        <div class="page-title-subheading">รายการใบสำคัญรับทั้งหมด</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('receipt-voucher.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> สร้างใบสำคัญรับ
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="filter-section">
                    <form method="GET" action="{{ route('receipt-voucher.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach (\App\Models\ReceiptVoucher::$statusLabels as $status_value => $status_text)
                                    <option value="{{ $status_value }}" {{ request('status') == $status_value ? 'selected' : '' }}>
                                        {{ $status_text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">จากวันที่</label>
                            <input type="text" name="from_date" class="form-control datepicker"
                                value="{{ request('from_date') }}" placeholder="วว/ดด/ปปปป">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">ถึงวันที่</label>
                            <input type="text" name="to_date" class="form-control datepicker"
                                value="{{ request('to_date') }}" placeholder="วว/ดด/ปปปป">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ค้นหา</label>
                            <input type="text" name="search" class="form-control" placeholder="เลขที่/รับจาก/หมายเหตุ..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="fa fa-search"></i> ค้นหา</button>
                            <a href="{{ route('receipt-voucher.index') }}" class="btn btn-secondary"><i class="fa fa-refresh"></i> รีเซ็ต</a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">เลขที่</th>
                                <th width="10%">วันที่</th>
                                <th>รับจาก</th>
                                <th width="12%" class="text-end">จำนวนเงิน</th>
                                <th width="10%">สถานะ</th>
                                <th width="12%">ผู้สร้าง</th>
                                <th width="15%">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $index => $voucher)
                                <tr>
                                    <td>{{ $vouchers->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('receipt-voucher.show', $voucher->id) }}" class="fw-bold text-primary">
                                            {{ $voucher->voucher_no }}
                                        </a>
                                    </td>
                                    <td>{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($voucher->receive_from, 30) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($voucher->net_amount, 2) }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $voucher->status }}">
                                            {{ $voucher->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $voucher->creator->name ?? $voucher->created_by_name }}</td>
                                    <td>
                                        <a href="{{ route('receipt-voucher.show', $voucher->id) }}" class="btn btn-info btn-action" title="ดูรายละเอียด">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if ($voucher->canEdit())
                                            <a href="{{ route('receipt-voucher.edit', $voucher->id) }}" class="btn btn-warning btn-action" title="แก้ไข">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('receipt-voucher.destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-action" title="ลบ"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @endif
                                        @can('receipt-voucher.delete')
                                            <form action="{{ route('receipt-voucher.force-destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบถาวร? การดำเนินการนี้ไม่สามารถกู้คืนได้')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-action" title="ลบถาวร"><i class="fa fa-fire"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">ไม่พบข้อมูลใบสำคัญรับ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $vouchers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
