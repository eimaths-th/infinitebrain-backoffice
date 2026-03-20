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

        .text-nowrap {
            white-space: nowrap;
        }

        .action-container {
            display: flex;
            gap: 2px;
            flex-wrap: nowrap;
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
                            <label class="form-label">กลุ่มสาขา</label>
                            <select name="branch_group" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach ($groupBranchCodes as $group_name => $codes)
                                    <option value="{{ $group_name }}"
                                        {{ request('branch_group') == $group_name ? 'selected' : '' }}>
                                        {{ $group_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">สาขา</label>
                            <select name="branch_code" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach ($branchCodes as $code => $name)
                                    <option value="{{ $code }}"
                                        {{ request('branch_code') == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach (\App\Models\PaymentVoucher::$statusLabels as $status_value => $status_text)
                                    <option value="{{ $status_value }}"
                                        {{ request('status') == $status_value ? 'selected' : '' }}>
                                        {{ $status_text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">หมวดหมู่</label>
                            <select name="category" class="form-select">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach (\App\Models\PaymentVoucher::$categoryLabels as $cat_value => $cat_text)
                                    <option value="{{ $cat_value }}"
                                        {{ request('category') == $cat_value ? 'selected' : '' }}>
                                        {{ $cat_text }}
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
                                <th width="3%">#</th>
                                <th width="13%" class="text-nowrap">เลขที่</th>
                                <th width="10%" class="text-nowrap">วันที่</th>
                                <th width="14%">หมวดหมู่</th>
                                <th width="16%">จ่ายให้</th>
                                <th width="10%" class="text-end text-nowrap">จำนวนเงิน</th>
                                <th width="10%" class="text-nowrap">สถานะ</th>
                                <th width="12%">ผู้สร้าง</th>
                                <th width="12%" class="text-nowrap">การดำเนินการ</th>
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

                                            @if ($voucher->is_salary)
                                                <i class="fa fa-money-bill text-success" title="ใบจ่ายเงินเดือน"></i>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($voucher->category)
                                            <span class="badge bg-light text-dark border text-nowrap">{{ $voucher->category_label }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($voucher->pay_to, 40) }}</td>
                                    <td class="text-end text-nowrap fw-bold">{{ number_format($voucher->total_amount, 2) }}</td>
                                    <td class="text-nowrap">
                                        <span class="status-badge status-{{ $voucher->status }}">
                                            {{ $voucher->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $voucher->created_by_name }}</td>
                                    <td class="text-nowrap">
                                        <div class="action-container">
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
                                            {{-- ปุ่มแก้ไขหมวดหมู่พิเศษ (ใช้ได้ทุกสถานะ) --}}
                                            <button type="button" class="btn btn-outline-primary btn-action btn-edit-category"
                                                data-id="{{ $voucher->id }}" 
                                                data-category="{{ $voucher->category }}"
                                                data-voucher-no="{{ $voucher->voucher_no }}"
                                                data-is-salary="{{ $voucher->is_salary ? '1' : '0' }}"
                                                title="แก้ไขหมวดหมู่">
                                                <i class="fa fa-tags"></i>
                                            </button>
                                        </div>
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

    {{-- Modal แก้ไขหมวดหมู่พิเศษ --}}
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขหมวดหมู่พิเศษ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            ใบสำคัญจ่ายเลขที่: <strong id="modalVoucherNo"></strong>
                        </div>
                        <div id="salaryWarning" class="alert alert-warning py-2 mb-3 d-none">
                            <i class="fa fa-info-circle"></i> รายการนี้เป็นใบจ่ายเงินเดือน ไม่สามารถเลือกหมวดหมู่ได้
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมวดหมู่</label>
                            <select name="category" id="modalCategorySelect" class="form-select">
                                <option value="">-- ไม่ระบุ --</option>
                                @foreach (\App\Models\PaymentVoucher::$categoryLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
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

            // จัดการ Modal แก้ไขหมวดหมู่
            $('.btn-edit-category').on('click', function() {
                const id = $(this).data('id');
                const category = $(this).data('category');
                const voucherNo = $(this).data('voucher-no');
                const isSalary = $(this).data('is-salary');
                
                const form = $('#editCategoryForm');
                const url = "{{ route('payment-voucher.update-category', ':id') }}".replace(':id', id);
                
                form.attr('action', url);
                $('#modalVoucherNo').text(voucherNo);
                $('#modalCategorySelect').val(category).prop('disabled', isSalary == '1');
                
                if (isSalary == '1') {
                    $('#salaryWarning').removeClass('d-none');
                } else {
                    $('#salaryWarning').addClass('d-none');
                }
                
                $('#editCategoryModal').modal('show');
            });
        });
    </script>
@endsection
