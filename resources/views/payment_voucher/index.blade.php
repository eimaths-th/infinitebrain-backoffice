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
    .text-meta{
        color: #ffc107;
    }
    .text-eimaths{
        color: #ff9822;
    }
    .text-infinitebrain{
        color: #28a745;
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
                            <option value="{{ $group_name }}" {{ request('branch_group') == $group_name ? 'selected' : '' }}>
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
                            <option value="{{ $code }}" {{ request('branch_code') == $code ? 'selected' : '' }}>
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
                            <option value="{{ $status_value }}" {{ request('status') == $status_value ? 'selected' : '' }}>
                                {{ $status_text }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">หมวดหมู่</label>
                        <select name="category" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            @foreach (\App\Models\PaymentVoucher::getAllCategoryLabels() as $cat_value => $cat_text)
                            <option value="{{ $cat_value }}" {{ request('category') == $cat_value ? 'selected' : '' }}>
                                {{ $cat_text }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">จากวันที่</label>
                        <input type="text" name="from_date" class="form-control datepicker" value="{{ request('from_date') }}" placeholder="วว/ดด/ปปปป">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ถึงวันที่</label>
                        <input type="text" name="to_date" class="form-control datepicker" value="{{ request('to_date') }}" placeholder="วว/ดด/ปปปป">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ค้นหา</label>
                        <input type="text" name="search" class="form-control" placeholder="เลขที่/ผู้รับเงิน..." value="{{ request('search') }}">
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

            <!-- Action Panel -->
            <div class="mb-3 d-none" id="bulk-action-panel">
                <form action="{{ route('payment-voucher.bulk-action') }}" method="POST" id="bulkActionForm" class="d-inline">
                    @csrf
                    <input type="hidden" name="action" id="bulk_action_type" value="">
                    <div id="bulk_voucher_ids"></div>
                    @can('payment-voucher.evaluation.create')
                    <button type="button" class="btn btn-success me-2 d-none" id="btn-bulk-evaluate" onclick="openBulkConfirmModal('evaluate', 'เข้าใบประเมินหลายรายการ')">
                        <i class="fa fa-clipboard-check"></i> เข้าใบประเมินหลายรายการ
                    </button>
                    @endcan
                    <button type="button" class="btn btn-info me-2 d-none" id="btn-bulk-review" onclick="openBulkConfirmModal('review', 'ตรวจสอบหลายรายการ')">
                        <i class="fa fa-search"></i> ตรวจสอบหลายรายการ
                    </button>
                    <button type="button" class="btn btn-success me-2 d-none" id="btn-bulk-approve" onclick="openBulkConfirmModal('approve', 'อนุมัติหลายรายการ')">
                        <i class="fa fa-check"></i> อนุมัติหลายรายการ
                    </button>
                    <button type="button" class="btn btn-primary me-2 d-none" id="btn-bulk-complete" onclick="openBulkConfirmModal('complete', 'บันทึกเสร็จสิ้นหลายรายการ')">
                        <i class="fa fa-flag-checkered"></i> บันทึกเสร็จสิ้นหลายรายการ
                    </button>
                </form>
            </div>

            <!-- Bulk Confirm Modal -->
            <div class="modal fade" id="bulkConfirmModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white" id="bulkConfirmHeader">
                            <h5 class="modal-title" id="bulkConfirmTitle">ยืนยันการทำรายการ</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2">คุณกำลังเลือกทำรายการใบสำคัญจ่ายต่อไปนี้:</p>
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>เลขที่</th>
                                            <th>วันที่</th>
                                            <th>จ่ายให้</th>
                                            <th class="text-end">จำนวนเงิน</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bulkConfirmList">
                                        <!-- List goes here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">รวมจำนวนเงินทั้งสิ้น</th>
                                            <th id="bulkConfirmTotal" class="text-end text-danger fs-5">0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div id="completeInputSection" class="d-none border-top pt-3">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อผู้จ่ายเงิน <span class="text-danger">*</span></label>
                                    <input type="text" id="bulk_payer_name" class="form-control" value="{{ Auth::user()->name }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">วันที่จ่ายเงิน</label>
                                    <input type="text" id="bulk_paid_date" class="form-control datepicker" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="button" class="btn btn-primary" id="btnSubmitBulk" onclick="submitBulkAction()">ยืนยันทำรายการ</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="voucherTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="3%">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </th>
                            <th width="3%">#</th>
                            <th width="13%" class="text-nowrap">เลขที่</th>
                            <th width="10%" class="text-nowrap">วันที่</th>
                            <th width="14%">หมวดหมู่</th>
                            <th width="16%">จ่ายให้</th>
                            <th width="10%" class="text-end text-nowrap">จำนวนเงิน</th>
                            <th width="10%" class="text-nowrap">สถานะ</th>
                            <th width="8%" class="text-nowrap text-center">เข้าประเมิน</th>
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
                        @php
                            $class = '';
                            if ($voucher->group_branch_code == 'META') {
                                # code...
                                $class = 'text-meta';
                            }elseif ($voucher->group_branch_code == 'EiMaths') {
                                $class = 'text-eimaths';
                            }else {
                                $class = 'text-infinitebrain';
                            }

                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input voucher-checkbox" value="{{ $voucher->id }}" data-status="{{ $voucher->status }}" data-no="{{ $voucher->voucher_no }}" data-date="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}" data-payee="{{ $voucher->pay_to }}" data-amount="{{ $voucher->total_amount }}">
                            </td>
                            <td>{{ $vouchers->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('payment-voucher.show', $voucher->id) }}" class="fw-bold {{ $class }}">
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
                            <td class="text-center align-middle">
                                @if ($voucher->is_evaluation)
                                <span class="badge bg-success" title="เข้าใบประเมินแล้ว"><i class="fa fa-check"></i></span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $voucher->created_by_name }}</td>
                            <td class="text-nowrap">
                                <div class="action-container">
                                    <a href="{{ route('payment-voucher.show', $voucher->id) }}" class="btn btn-info btn-action" title="ดูรายละเอียด">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if ($voucher->canEdit())
                                    <a href="{{ route('payment-voucher.edit', $voucher->id) }}" class="btn btn-warning btn-action" title="แก้ไข">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('payment-voucher.destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-action" title="ลบ">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    {{-- ปุ่มลบถาวร (Force Delete) - สำหรับ admin หรือผู้มีสิทธิ์ --}}
                                    @can('payment-voucher.delete')
                                    <form action="{{ route('payment-voucher.force-destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('⚠️ คำเตือน!\n\nการลบนี้จะลบข้อมูลทั้งหมดที่เกี่ยวข้อง:\n- รายการย่อยทั้งหมด\n- ประวัติการดำเนินการ\n- ไฟล์แนบทั้งหมด\n\nการดำเนินการนี้ไม่สามารถกู้คืนได้!\n\nยืนยันการลบถาวร?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-dark btn-action" title="ลบถาวร">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @if ($voucher->canApprove())
                                    <button type="button" class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#approveModal{{ $voucher->id }}" title="อนุมัติ" {{ $enableFinance }}>
                                        <i class="fa fa-check"></i>
                                    </button>
                                    @endif
                                    {{-- ปุ่มแก้ไขหมวดหมู่พิเศษ (ใช้ได้ทุกสถานะ) --}}
                                    <button type="button" class="btn btn-outline-primary btn-action btn-edit-category" data-id="{{ $voucher->id }}" data-category="{{ $voucher->category }}" data-voucher-no="{{ $voucher->voucher_no }}" data-is-salary="{{ $voucher->is_salary ? '1' : '0' }}" title="แก้ไขหมวดหมู่">
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
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" {{ $enableFinance }}></button>
                                    </div>
                                    <form action="{{ route('payment-voucher.approve', $voucher->id) }}" method="POST">
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
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
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
                            @foreach (\App\Models\PaymentVoucher::getAllCategoryLabels() as $value => $label)
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

        // Bulk Checkbox Logic
        $('#checkAll').change(function() {
            $('.voucher-checkbox').prop('checked', $(this).prop('checked'));
            evaluateBulkActions();
        });

        $('.voucher-checkbox').change(function() {
            if ($('.voucher-checkbox:checked').length === $('.voucher-checkbox').length) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
            evaluateBulkActions();
        });
    });

    const canReview = @json(Auth::user()->can('payment-voucher.review'));
    const canApprove = @json(Auth::user()->can('payment-voucher.approve'));

    function evaluateBulkActions() {
        let statuses = new Set();
        let checkedItems = $('.voucher-checkbox:checked');

        checkedItems.each(function() {
            statuses.add($(this).data('status'));
        });

        $('#btn-bulk-review').addClass('d-none');
        $('#btn-bulk-approve').addClass('d-none');
        $('#btn-bulk-complete').addClass('d-none');
        $('#btn-bulk-evaluate').addClass('d-none');

        if (checkedItems.length > 0) {
            $('#bulk-action-panel').removeClass('d-none');
            $('#btn-bulk-evaluate').removeClass('d-none');

            // ถ้ารายการที่เลือกมีสถานะเดียวกันทั้งหมด
            if (statuses.size === 1) {
                const status = Array.from(statuses)[0];
                if (status === 'pending' && canReview) {
                    $('#btn-bulk-review').removeClass('d-none');
                } else if (status === 'reviewed' && canApprove) {
                    $('#btn-bulk-approve').removeClass('d-none');
                } else if (status === 'approved') {
                    $('#btn-bulk-complete').removeClass('d-none');
                }
            }
        } else {
            $('#bulk-action-panel').addClass('d-none');
        }
    }

    function openBulkConfirmModal(action, title) {
        $('#bulk_action_type').val(action);
        $('#bulkConfirmTitle').text(title);

        // Set styles based on action
        let headerClass = 'bg-primary';
        let btnClass = 'btn-primary';

        if (action === 'review') {
            headerClass = 'bg-info';
            btnClass = 'btn-info text-white';
        } else if (action === 'approve') {
            headerClass = 'bg-success';
            btnClass = 'btn-success';
        } else if (action === 'evaluate') {
            headerClass = 'bg-success';
            btnClass = 'btn-success';
        }

        $('#bulkConfirmHeader').removeClass('bg-primary bg-info bg-success').addClass(headerClass);
        $('#btnSubmitBulk').removeClass('btn-primary btn-info btn-success text-white').addClass(btnClass);

        // Generate list
        let listHtml = '';
        let totalAmount = 0;

        $('.voucher-checkbox:checked').each(function() {
            const no = $(this).data('no');
            const date = $(this).data('date');
            const payee = $(this).data('payee');
            const amount = parseFloat($(this).data('amount'));

            totalAmount += amount;

            listHtml += `
                    <tr>
                        <td><span class="badge ${headerClass}">${no}</span></td>
                        <td>${date}</td>
                        <td>${payee}</td>
                        <td class="text-end">${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                `;
        });

        $('#bulkConfirmList').html(listHtml);
        $('#bulkConfirmTotal').text(totalAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2
            , maximumFractionDigits: 2
        }));

        if (action === 'complete') {
            $('#completeInputSection').removeClass('d-none');
            $('#bulk_payer_name').prop('required', true);
        } else {
            $('#completeInputSection').addClass('d-none');
            $('#bulk_payer_name').prop('required', false);
        }

        $('#bulkConfirmModal').modal('show');
    }

    function submitBulkAction() {
        const action = $('#bulk_action_type').val();

        if (action === 'complete') {
            const payerName = $('#bulk_payer_name').val();
            const paidDate = $('#bulk_paid_date').val();

            if (!payerName) {
                alert('กรุณาระบุชื่อผู้จ่ายเงิน');
                return;
            }

            // Add extra fields to form
            $('#bulkActionForm input[name="payer_name"]').remove();
            $('#bulkActionForm input[name="paid_date"]').remove();

            let extraFields = `
                    <input type="hidden" name="payer_name" value="${payerName}">
                    <input type="hidden" name="paid_date" value="${paidDate}">
                `;
            $('#bulkActionForm').append(extraFields);
        }

        let idsHtml = '';
        $('.voucher-checkbox:checked').each(function() {
            idsHtml += `<input type="hidden" name="voucher_ids[]" value="${$(this).val()}">`;
        });
        $('#bulk_voucher_ids').html(idsHtml);

        $('#bulkActionForm').submit();
    }

</script>
@endsection
