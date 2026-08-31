@extends('layouts.main_template')

@section('style')
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .text-meta {
            color: #ffc107;
        }

        .text-eimaths {
            color: #ff9822;
        }

        .text-infinitebrain {
            color: #28a745;
        }

        .btn-action {
            padding: 3px 8px;
            font-size: 12px;
            margin: 1px;
        }

        .pending-alert {
            background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
            color: #212529;
            padding: 20px;
            border-radius: 10px;
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

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-clock icon-gradient bg-sunny-morning"></i>
                    </div>
                    <div>
                        รายการรออนุมัติ
                        <div class="page-title-subheading">ใบสำคัญจ่ายที่รอการอนุมัติ</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('payment-voucher.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Alert -->
        <div class="pending-alert">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fa fa-clock-o"></i> มีใบสำคัญจ่ายรออนุมัติ {{ $vouchers->total() }} รายการ
                    </h5>
                    <p class="mb-0">กรุณาตรวจสอบและดำเนินการอนุมัติหรือปฏิเสธ</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-dark fs-5">{{ $vouchers->total() }}</span>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <!-- Action Panel -->
                <div class="mb-3 d-none" id="bulk-action-panel">
                    <form action="{{ route('payment-voucher.bulk-action') }}" method="POST" id="bulkActionForm" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" id="bulk_action_type" value="">
                        <div id="bulk_voucher_ids"></div>
                        <button type="button" class="btn btn-success me-2" id="btn-bulk-approve" onclick="openBulkConfirmModal('approve', 'อนุมัติใบสำคัญจ่ายที่เลือก')">
                            <i class="fa fa-check"></i> อนุมัติหลายรายการ
                        </button>
                    </form>
                </div>

                <!-- Bulk Confirm Modal -->
                <div class="modal fade" id="bulkConfirmModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="bulkConfirmTitle">ยืนยันการทำรายการ</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">คุณกำลังเลือกทำรายการใบสำคัญจ่ายต่อไปนี้:</p>
                                <div class="table-responsive">
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="button" class="btn btn-success" onclick="submitBulkAction()">ยืนยันทำรายการ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-warning">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th width="3%">#</th>
                                <th width="12%">เลขที่</th>
                                <th width="10%">วันที่</th>
                                <th width="25%">จ่ายให้</th>
                                <th width="15%" class="text-end">จำนวนเงิน</th>
                                <th width="15%">ผู้ตรวจสอบ</th>
                                <th width="18%">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $index => $voucher)
                                @php
                                    $voucher_color_class = 'text-infinitebrain';

                                    if ($voucher->group_branch_code == 'META') {
                                        $voucher_color_class = 'text-meta';
                                    } elseif ($voucher->group_branch_code == 'EiMaths') {
                                        $voucher_color_class = 'text-eimaths';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input voucher-checkbox" 
                                            value="{{ $voucher->id }}"
                                            data-no="{{ $voucher->voucher_no }}"
                                            data-date="{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}"
                                            data-payee="{{ $voucher->pay_to }}"
                                            data-amount="{{ $voucher->net_amount }}">
                                    </td>
                                    <td>{{ $vouchers->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('payment-voucher.show', $voucher->id) }}"
                                            class="fw-bold {{ $voucher_color_class }}">
                                            {{ $voucher->voucher_no }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                                    <td>{{ $voucher->pay_to }}</td>
                                    <td class="text-end">{{ number_format($voucher->net_amount, 2) }}</td>
                                    <td>{{ $voucher->reviewed_by_name }}</td>
                                    <td>
                                        <a href="{{ route('payment-voucher.show', $voucher->id) }}"
                                            class="btn btn-info btn-action" title="ดูรายละเอียด">
                                            <i class="fa fa-eye"></i> ดู
                                        </a>
                                        <button type="button" class="btn btn-success btn-action" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $voucher->id }}" title="อนุมัติ">
                                            <i class="fa fa-check"></i> อนุมัติ
                                        </button>
                                        <button type="button" class="btn btn-danger btn-action" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $voucher->id }}" title="ปฏิเสธ">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $voucher->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">อนุมัติใบสำคัญจ่าย</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment-voucher.approve', $voucher->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p><strong>เลขที่:</strong> {{ $voucher->voucher_no }}</p>
                                                    <p><strong>จ่ายให้:</strong> {{ $voucher->pay_to }}</p>
                                                    <p><strong>จำนวนเงิน:</strong>
                                                        {{ number_format($voucher->net_amount, 2) }} บาท</p>
                                                    <p><strong>ผู้ตรวจสอบ:</strong> {{ $voucher->reviewed_by_name }}</p>
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

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $voucher->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">ปฏิเสธใบสำคัญจ่าย</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment-voucher.reject', $voucher->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p><strong>เลขที่:</strong> {{ $voucher->voucher_no }}</p>
                                                    <p><strong>จ่ายให้:</strong> {{ $voucher->pay_to }}</p>
                                                    <p><strong>จำนวนเงิน:</strong>
                                                        {{ number_format($voucher->net_amount, 2) }} บาท</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">เหตุผลที่ปฏิเสธ <span
                                                                class="text-danger">*</span></label>
                                                        <textarea name="remark" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-danger">ปฏิเสธ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fa fa-check-circle fa-3x text-success mb-3 d-block"></i>
                                        ไม่มีรายการรออนุมัติ
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
                        {{ $vouchers->links() }}
                    </div>
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
        });

        function evaluateBulkActions() {
            let checkedItems = $('.voucher-checkbox:checked');

            if (checkedItems.length > 0) {
                $('#bulk-action-panel').removeClass('d-none');
            } else {
                $('#bulk-action-panel').addClass('d-none');
            }
        }

        function openBulkConfirmModal(action, title) {
            $('#bulk_action_type').val(action);
            $('#bulkConfirmTitle').text(title);
            
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
                        <td><span class="badge bg-primary">${no}</span></td>
                        <td>${date}</td>
                        <td>${payee}</td>
                        <td class="text-end">${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
            
            $('#bulkConfirmList').html(listHtml);
            $('#bulkConfirmTotal').text(totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            $('#bulkConfirmModal').modal('show');
        }

        function submitBulkAction() {
            let idsHtml = '';
            $('.voucher-checkbox:checked').each(function() {
                idsHtml += `<input type="hidden" name="voucher_ids[]" value="${$(this).val()}">`;
            });
            $('#bulk_voucher_ids').html(idsHtml);
            
            $('#bulkActionForm').submit();
        }
    </script>
@endsection
