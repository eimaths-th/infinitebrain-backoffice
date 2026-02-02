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

        .status-draft {
            background-color: #6c757d;
            color: white;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-approved {
            background-color: #17a2b8;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .status-reviewed {
            background-color: #007bff;
            color: white;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .btn-action {
            padding: 3px 8px;
            font-size: 12px;
            margin: 1px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                        ใบสำคัญจ่าย (Payment Voucher)
                        <div class="page-title-subheading">รายการใบสำคัญจ่ายทั้งหมด</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('payment-voucher.import') }}" class="btn btn-info">
                        <i class="fa fa-upload"></i> Import Excel
                    </a>
                    <a href="{{ route('payment-voucher.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> สร้างใบสำคัญจ่าย
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" action="{{ route('payment-voucher.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>ร่าง</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รออนุมัติ
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ
                                </option>
                                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>ตรวจสอบแล้ว
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    เสร็จสิ้น
                                </option>
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
                            <input type="text" name="search" class="form-control" placeholder="เลขที่/ผู้รับเงิน..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-search"></i> ค้นหา
                            </button>
                            <a href="{{ route('payment-voucher.index') }}" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i> รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="voucherTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">เลขที่</th>
                                <th width="10%">วันที่</th>
                                <th width="20%">จ่ายให้</th>
                                <th width="12%" class="text-end">จำนวนเงิน</th>
                                <th width="10%">สถานะ</th>
                                <th width="12%">ผู้สร้าง</th>
                                <th width="19%">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // ใช้ระบบ permission แทน level
                                $canReview = Auth::user()->can('payment-voucher.review');
                                $canApprove = Auth::user()->can('payment-voucher.approve');
                                $disableAccount = $canReview ? '' : 'disabled';
                                $enableFinance = $canApprove ? '' : 'disabled';
                            @endphp
                            @forelse($vouchers as $index => $voucher)
                                <tr>
                                    <td>{{ $vouchers->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('payment-voucher.show', $voucher->id) }}"
                                            class="fw-bold text-primary">
                                            {{ $voucher->voucher_no }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($voucher->pay_to, 30) }}</td>
                                    <td class="text-end">{{ number_format($voucher->total_amount, 2) }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $voucher->status }}">
                                            {{ $voucher->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $voucher->created_by_name }}</td>
                                    <td>
                                        <a href="{{ route('payment-voucher.show', $voucher->id) }}"
                                            class="btn btn-info btn-action" title="ดูรายละเอียด">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if ($voucher->canEdit())
                                            <a href="{{ route('payment-voucher.edit', $voucher->id) }}"
                                                class="btn btn-warning btn-action" title="แก้ไข">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('payment-voucher.destroy', $voucher->id) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-action" title="ลบ">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        {{-- ปุ่มลบถาวร (Force Delete) - สำหรับ admin หรือผู้มีสิทธิ์ --}}
                                        @can('payment-voucher.delete')
                                            <form action="{{ route('payment-voucher.force-destroy', $voucher->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('⚠️ คำเตือน!\n\nการลบนี้จะลบข้อมูลทั้งหมดที่เกี่ยวข้อง:\n- รายการย่อยทั้งหมด\n- ประวัติการดำเนินการ\n- ไฟล์แนบทั้งหมด\n\nการดำเนินการนี้ไม่สามารถกู้คืนได้!\n\nยืนยันการลบถาวร?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-dark btn-action" title="ลบถาวร">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @if ($voucher->canApprove())
                                            <button type="button" class="btn btn-success btn-action"
                                                data-bs-toggle="modal" data-bs-target="#approveModal{{ $voucher->id }}"
                                                title="อนุมัติ" {{ $enableFinance }}>
                                                <i class="fa fa-check"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Approve Modal -->
                                @if ($voucher->canApprove())
                                    <div class="modal fade" id="approveModal{{ $voucher->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">อนุมัติใบสำคัญจ่าย</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal" {{ $enableFinance }}></button>
                                                </div>
                                                <form action="{{ route('payment-voucher.approve', $voucher->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p><strong>เลขที่:</strong> {{ $voucher->voucher_no }}</p>
                                                        <p><strong>จ่ายให้:</strong> {{ $voucher->pay_to }}</p>
                                                        <p><strong>จำนวนเงิน:</strong>
                                                            {{ number_format($voucher->total_amount, 2) }} บาท</p>
                                                        <div class="mb-3">
                                                            <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                                                            <textarea name="remark" class="form-control" rows="2"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">ยกเลิก</button>
                                                        <button type="submit" class="btn btn-success">อนุมัติ</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fa fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        ไม่พบข้อมูลใบสำคัญจ่าย
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        แสดง {{ $vouchers->firstItem() ?? 0 }} - {{ $vouchers->lastItem() ?? 0 }} จาก
                        {{ $vouchers->total() }} รายการ
                    </div>
                    <div>
                        {{ $vouchers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@endsection
