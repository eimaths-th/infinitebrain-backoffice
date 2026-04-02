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

                <!-- Action Panel -->
                <div class="mb-3 d-none" id="bulk-action-panel">
                    <form action="{{ route('receipt-voucher.bulk-action') }}" method="POST" id="bulkActionForm" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" id="bulk_action_type" value="">
                        <div id="bulk_voucher_ids"></div>
                        <button type="button" class="btn btn-warning me-2 d-none" id="btn-bulk-verify" onclick="openBulkConfirmModal('verify', 'ตรวจสอบหลายรายการ')">
                            <i class="fa fa-search"></i> ตรวจสอบหลายรายการ
                        </button>
                        <button type="button" class="btn btn-success me-2 d-none" id="btn-bulk-complete" onclick="openBulkConfirmModal('complete', 'บันทึกเสร็จสิ้นหลายรายการ')">
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
                                <p class="mb-2">คุณกำลังเลือกทำรายการใบสำคัญรับต่อไปนี้:</p>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>เลขที่</th>
                                                <th>วันที่</th>
                                                <th>รับจาก</th>
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
                                        <label class="form-label">ผู้รับเงิน <span class="text-danger">*</span></label>
                                        <input type="text" id="bulk_receiver_name" class="form-control" value="{{ Auth::user()->name }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">วันที่รับเงิน</label>
                                        <input type="text" id="bulk_received_date" class="form-control datepicker" value="{{ date('Y-m-d') }}">
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

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th width="3%">#</th>
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
                            @php
                                $canVerify = Auth::user()->can('receipt-voucher.verify');
                                $canComplete = Auth::user()->can('receipt-voucher.complete');
                            @endphp
                            @forelse($vouchers as $index => $voucher)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input voucher-checkbox" 
                                            value="{{ $voucher->id }}" 
                                            data-status="{{ $voucher->status }}"
                                            data-no="{{ $voucher->voucher_no }}"
                                            data-date="{{ $voucher->voucher_date->format('d/m/Y') }}"
                                            data-payee="{{ $voucher->receive_from }}"
                                            data-amount="{{ $voucher->net_amount }}">
                                    </td>
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

@section('script')
    <script>
        $(document).ready(function() {
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
            
            // Re-evalute on load in case of going back from history
            evaluateBulkActions();
        });

        const canVerify = @json(Auth::user()->can('receipt-voucher.verify'));
        const canComplete = @json(Auth::user()->can('receipt-voucher.complete'));

        function evaluateBulkActions() {
            let statuses = new Set();
            let checkedItems = $('.voucher-checkbox:checked');
            
            checkedItems.each(function() {
                statuses.add($(this).data('status'));
            });

            $('#btn-bulk-verify').addClass('d-none');
            $('#btn-bulk-complete').addClass('d-none');

            if (checkedItems.length > 0) {
                $('#bulk-action-panel').removeClass('d-none');
                
                // ถ้ารายการที่เลือกมีสถานะเดียวกันทั้งหมด
                if (statuses.size === 1) {
                    const status = Array.from(statuses)[0];
                    if (status === 'pending' && canVerify) {
                        $('#btn-bulk-verify').removeClass('d-none');
                    } else if (status === 'verified' && canComplete) {
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
            
            if (action === 'verify') {
                headerClass = 'bg-warning text-dark';
                btnClass = 'btn-warning text-dark';
            } else if (action === 'complete') {
                headerClass = 'bg-success';
                btnClass = 'btn-success text-white';
            }
            
            $('#bulkConfirmHeader').removeClass('bg-primary bg-warning bg-success text-dark text-white').addClass(headerClass);
            $('#btnSubmitBulk').removeClass('btn-primary btn-warning btn-success text-dark text-white').addClass(btnClass);
            
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
            $('#bulkConfirmTotal').text(totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            if (action === 'complete') {
                $('#completeInputSection').removeClass('d-none');
                $('#bulk_receiver_name').prop('required', true);
            } else {
                $('#completeInputSection').addClass('d-none');
                $('#bulk_receiver_name').prop('required', false);
            }
            
            $('#bulkConfirmModal').modal('show');
        }

        function submitBulkAction() {
            const action = $('#bulk_action_type').val();
            
            if (action === 'complete') {
                const receiverName = $('#bulk_receiver_name').val();
                const receivedDate = $('#bulk_received_date').val();

                if (!receiverName) {
                    alert('กรุณาระบุชื่อผู้รับเงิน');
                    return;
                }
                
                // Add extra fields to form
                $('#bulkActionForm input[name="receiver_name"]').remove();
                $('#bulkActionForm input[name="received_date"]').remove();
                
                let extraFields = `
                    <input type="hidden" name="receiver_name" value="${receiverName}">
                    <input type="hidden" name="received_date" value="${receivedDate}">
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
