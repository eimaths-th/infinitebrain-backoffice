@extends('layouts.main_template')

@section('style')
    <style>
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-draft { background-color: #6c757d; color: white; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-verified { background-color: #007bff; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }

        .voucher-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .voucher-number {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            height: 100%;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1rem;
            color: #212529;
        }

        .items-table th {
            background: #343a40;
            color: white;
        }

        .total-row {
            background: #e7f3ff;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            position: relative;
            padding-left: 40px;
            padding-bottom: 20px;
            border-left: 2px solid #dee2e6;
        }

        .timeline-item:last-child {
            border-left: none;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #007bff;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #007bff;
        }

        .timeline-action {
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
            font-size: 0.95rem;
        }

        .timeline-user {
            font-size: 0.85rem;
            color: #495057;
            display: block;
            margin-bottom: 3px;
        }

        .timeline-time {
            font-size: 0.75rem;
            color: #6c757d;
            display: block;
            margin-bottom: 8px;
        }

        .approval-card {
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            background: #f8fff9;
        }

        .attachment-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .attachment-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            width: 150px;
            background: #f8f9fa;
        }

        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="app-page-title no-print">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-note2 icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        ใบสำคัญรับ
                        <div class="page-title-subheading">รายละเอียดใบสำคัญรับ</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </button>
                    <a href="{{ route('receipt-voucher.print-pdf', $voucher->id) }}" target="_blank"
                        class="btn btn-primary">
                        <i class="fa fa-print"></i> พิมพ์
                    </a>
                    @if ($voucher->canEdit())
                        <a href="{{ route('receipt-voucher.edit', $voucher->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> แก้ไข
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <!-- Voucher Header -->
            <div class="voucher-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="voucher-number">{{ $voucher->voucher_no }}</div>
                        <div>วันที่: {{ $voucher->voucher_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="status-badge status-{{ $voucher->status }}">
                            {{ $voucher->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <!-- Payee Info -->
            <div class="info-card">
                <h6 class="border-bottom pb-2 mb-3"><i class="fa fa-user"></i> ข้อมูลผู้รับเงิน / รับจาก</h6>
                <div class="mb-2">
                    <div class="info-label">รับจาก</div>
                    <div class="info-value">{{ $voucher->receive_from }}</div>
                </div>
                @if ($voucher->receive_from_address)
                    <div class="mb-2">
                        <div class="info-label">ที่อยู่</div>
                        <div class="info-value">{{ $voucher->receive_from_address }}</div>
                    </div>
                @endif
                @if ($voucher->receive_from_account_no || $voucher->receive_from_bank_name)
                    <div class="row">
                        @if ($voucher->receive_from_account_no)
                            <div class="col-6 mb-2">
                                <div class="info-label">เลขที่บัญชี</div>
                                <div class="info-value">{{ $voucher->receive_from_account_no }}</div>
                            </div>
                        @endif
                        @if ($voucher->receive_from_bank_name)
                            <div class="col-6 mb-2">
                                <div class="info-label">ธนาคาร</div>
                                <div class="info-value">{{ $voucher->receive_from_bank_name }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <!-- Creator Info -->
            <div class="info-card">
                <h6 class="border-bottom pb-2 mb-3"><i class="fa fa-info-circle"></i> ข้อมูลเอกสาร</h6>
                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="info-label">ผู้สร้าง</div>
                        <div class="info-value">{{ $voucher->creator->name ?? $voucher->created_by_name }}</div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="info-label">วันที่สร้าง</div>
                        <div class="info-value">{{ $voucher->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="info-label">สาขา</div>
                        <div class="info-value">{{ $voucher->branch_code }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <!-- Items Table -->
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-list"></i> รายการ</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered items-table">
                            <thead>
                                <tr>
                                    <th width="8%" class="text-center">ลำดับ</th>
                                    <th>รายละเอียด</th>
                                    <th width="20%" class="text-end">จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($voucher->receiptItems as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="2" class="text-end">รวมเป็นเงิน</td>
                                    <td class="text-end text-success">{{ number_format($voucher->total_amount, 2) }} บ.</td>
                                </tr>
                                @if ($voucher->deductions->count() > 0)
                                    @foreach ($voucher->deductions as $deduction)
                                        <tr>
                                            <td colspan="2" class="text-end text-danger">
                                                <i class="fa fa-minus-circle"></i> หัก: {{ $deduction->description }}
                                            </td>
                                            <td class="text-end text-danger">
                                                -{{ number_format($deduction->amount, 2) }} บ.
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="total-row" style="background-color: #d1e7dd;">
                                        <td colspan="2" class="text-end">ยอดสุทธิ</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($voucher->net_amount, 2) }} บ.
                                        </td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    @if ($voucher->remark)
                        <div class="mt-3">
                            <strong>หมายเหตุ:</strong> {{ $voucher->remark }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <!-- Voucher Attachments -->
            @if ($voucher->attachments->count() > 0)
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa fa-paperclip"></i> เอกสารแนบ</h6>
                    </div>
                    <div class="card-body">
                        <div class="attachment-gallery">
                            @foreach ($voucher->attachments as $attachment)
                                <div class="attachment-card">
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                        @if ($attachment->isImage())
                                            <img src="{{ Storage::url($attachment->file_path) }}"
                                                style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                        @else
                                            <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                        @endif
                                    </a>
                                    <div class="mt-1 small text-truncate" title="{{ $attachment->original_name }}">
                                        {{ $attachment->original_name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12">
            <!-- Approval Actions -->
            @if ($voucher->canVerify() || $voucher->canComplete())
                <div class="main-card mb-3 card no-print">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-check-circle"></i> ดำเนินการ</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if ($voucher->canVerify())
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card" style="border-color: #17a2b8; background: #f0fafc;">
                                        <h6><i class="fa fa-search text-info"></i> ตรวจสอบใบสำคัญรับ</h6>
                                        <form action="{{ route('receipt-voucher.verify', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                                                <textarea name="remark" class="form-control" rows="2"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-info">
                                                <i class="fa fa-check"></i> ตรวจสอบเรียบร้อย
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card" style="border-color: #dc3545; background: #fff8f8;">
                                        <h6><i class="fa fa-thumbs-down text-danger"></i> ปฏิเสธใบสำคัญรับ</h6>
                                        <form action="{{ route('receipt-voucher.reject', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">เหตุผลที่ปฏิเสธ <span
                                                        class="text-danger">*</span></label>
                                                <textarea name="remark" class="form-control" rows="2" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa fa-times"></i> ปฏิเสธ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if ($voucher->canComplete())
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card">
                                        <h6><i class="fa fa-flag-checkered text-success"></i> บันทึกเสร็จสิ้น</h6>
                                        <form action="{{ route('receipt-voucher.complete', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <div class="mb-2">
                                                    <label class="form-label">ชื่อผู้รับเงิน / ผู้ดำเนินการภายนอก</label>
                                                    <input type="text" name="receiver_name" class="form-control" placeholder="เช่น นาย A">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">วันที่ดำเนินการเสร็จสิ้น</label>
                                                    <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                                                <textarea name="remark" class="form-control" rows="2"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-check"></i> บันทึกเสร็จสิ้น
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($voucher->status === \App\Models\ReceiptVoucher::STATUS_COMPLETED && Auth::user()->can('receipt-voucher.complete'))
                <div class="no-print mb-3">
                    <form action="{{ route('receipt-voucher.undo-complete', $voucher->id) }}" method="POST"
                        onsubmit="return confirm('ยืนยันการยกเลิกสถานะเสร็จสิ้น?')">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-undo"></i> ยกเลิกสถานะเสร็จสิ้น
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="col-12">
            <!-- Activity Log -->
            <div class="main-card mb-3 card no-print">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-history"></i> ประวัติการดำเนินการ</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach ($voucher->logs->sortByDesc('created_at') as $log)
                            <div class="timeline-item">
                                <div class="timeline-action">
                                    @switch($log->action)
                                        @case('create') <span class="text-primary">สร้างเอกสาร</span> @break
                                        @case('submit') <span class="text-warning">ส่งตรวจสอบ</span> @break
                                        @case('verify') <span class="text-info">ตรวจสอบแล้ว</span> @break
                                        @case('complete') <span class="text-success">เสร็จสิ้น</span> @break
                                        @case('reject') <span class="text-danger">ปฏิเสธ</span> @break
                                        @case('update') <span class="text-secondary">แก้ไข</span> @break
                                        @case('undo_complete') <span class="text-warning">ยกเลิกเสร็จสิ้น</span> @break
                                        @default <span>{{ $log->action }}</span>
                                    @endswitch
                                </div>
                                <div class="timeline-user">โดย {{ $log->user_name }}</div>
                                <div class="timeline-time">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                @if ($log->remark)
                                    <div class="text-muted small">{{ $log->remark }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
