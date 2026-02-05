@extends('layouts.main_template')

@section('style')
    <style>
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
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

        .status-reviewed {
            background-color: #17a2b8;
            color: white;
        }

        .status-approved {
            background-color: #28a745;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

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

        .print-section {
            display: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-section {
                display: block !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
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

        .attachment-card img {
            max-width: 130px;
            max-height: 130px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
        }

        .attachment-card .file-icon {
            width: 130px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .attachment-card .file-name {
            font-size: 0.75rem;
            margin-top: 8px;
            word-break: break-all;
            color: #6c757d;
        }

        .item-attachments-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .item-attachments-inline img {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            cursor: pointer;
        }

        .item-attachments-inline .pdf-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }

        /* Lightbox styles */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .lightbox .close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
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
                        ใบสำคัญจ่าย
                        <div class="page-title-subheading">รายละเอียดใบสำคัญจ่าย</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('payment-voucher.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                    <a href="{{ route('payment-voucher.export', $voucher->id) }}" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <a href="{{ route('payment-voucher.print-pdf', $voucher->id) }}" target="_blank"
                        class="btn btn-primary">
                        <i class="fa fa-print"></i> พิมพ์ (ฟอร์ม)
                    </a>
                    <button onclick="window.print()" class="btn btn-info">
                        <i class="fa fa-print"></i> พิมพ์ (หน้าเว็บ)
                    </button>
                    @if ($voucher->canEdit())
                        <a href="{{ route('payment-voucher.edit', $voucher->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> แก้ไข
                        </a>
                    @endif
                    {{-- ปุ่มลบถาวร (Force Delete) - สำหรับ admin หรือผู้มีสิทธิ์ --}}
                    @can('payment-voucher.delete')
                        <form action="{{ route('payment-voucher.force-destroy', $voucher->id) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('⚠️ คำเตือน!\n\nการลบนี้จะลบข้อมูลทั้งหมดที่เกี่ยวข้อง:\n- รายการย่อยทั้งหมด\n- ประวัติการดำเนินการ\n- ไฟล์แนบทั้งหมด\n\nการดำเนินการนี้ไม่สามารถกู้คืนได้!\n\nยืนยันการลบถาวร?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> ลบถาวร
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show">
                    <p class="mb-0">{{ $message }}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        @if ($message = Session::get('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show">
                    <p class="mb-0">{{ $message }}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <div class="col-12">
            <!-- Voucher Header -->
            <div class="voucher-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="voucher-number">{{ $voucher->voucher_no }}</div>
                        <div>วันที่: {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</div>
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
                <h6 class="border-bottom pb-2 mb-3"><i class="fa fa-user"></i> ข้อมูลผู้รับเงิน</h6>
                <div class="mb-2">
                    <div class="info-label">จ่ายให้</div>
                    <div class="info-value">{{ $voucher->pay_to }}</div>
                </div>
                @if ($voucher->pay_to_address)
                    <div class="mb-2">
                        <div class="info-label">ที่อยู่</div>
                        <div class="info-value">{{ $voucher->pay_to_address }}</div>
                    </div>
                @endif
                @if ($voucher->pay_to_account_no || $voucher->pay_to_bank_name)
                    <div class="row">
                        @if ($voucher->pay_to_account_no)
                            <div class="col-6 mb-2">
                                <div class="info-label">เลขที่บัญชี</div>
                                <div class="info-value">{{ $voucher->pay_to_account_no }}</div>
                            </div>
                        @endif
                        @if ($voucher->pay_to_bank_name)
                            <div class="col-6 mb-2">
                                <div class="info-label">ธนาคาร</div>
                                <div class="info-value">{{ $voucher->pay_to_bank_name }}</div>
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
                        <div class="info-value">{{ $voucher->created_by_name }}</div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="info-label">วันที่สร้าง</div>
                        <div class="info-value">{{ $voucher->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if ($voucher->approved_by_name)
                        <div class="col-6 mb-2">
                            <div class="info-label">ผู้อนุมัติ</div>
                            <div class="info-value">{{ $voucher->approved_by_name }}</div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="info-label">วันที่อนุมัติ</div>
                            <div class="info-value">
                                {{ $voucher->approved_at ? \Carbon\Carbon::parse($voucher->approved_at)->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>
                    @endif
                    @if ($voucher->reviewed_by_name)
                        <div class="col-6 mb-2">
                            <div class="info-label">ผู้ตรวจสอบ</div>
                            <div class="info-value">{{ $voucher->reviewed_by_name }}</div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="info-label">วันที่ตรวจสอบ</div>
                            <div class="info-value">
                                {{ $voucher->reviewed_at ? \Carbon\Carbon::parse($voucher->reviewed_at)->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>
                    @endif
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
                            <tr>
                                <th style="width: 150px;">วันที่:</th>
                                <td>{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                                <th style="width: 150px;">ผู้ทำรายการ:</th>
                                <td>{{ $voucher->created_by_name }}</td>
                            </tr>
                            <thead>
                                <tr>
                                    <th width="8%" class="text-center">ลำดับ</th>
                                    <th width="50%">รายละเอียด</th>
                                    <th width="25%">เอกสารแนบ</th>
                                    <th width="17%" class="text-end">จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($voucher->items as $item)
                                    <tr>
                                        <td class="text-center">{{ $item->item_no }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>
                                            @if ($item->attachments && $item->attachments->count() > 0)
                                                <div class="item-attachments-inline">
                                                    @foreach ($item->attachments as $attachment)
                                                        @if ($attachment->isImage())
                                                            <img src="{{ Storage::url($attachment->file_path) }}"
                                                                alt="{{ $attachment->original_name }}"
                                                                onclick="openLightbox('{{ Storage::url($attachment->file_path) }}')"
                                                                title="{{ $attachment->original_name }}">
                                                        @else
                                                            <a href="{{ Storage::url($attachment->file_path) }}"
                                                                target="_blank" title="{{ $attachment->original_name }}">
                                                                <div class="pdf-icon"><i
                                                                        class="fa fa-file-pdf-o text-danger"></i></div>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="3" class="text-end">รวมเป็นเงิน</td>
                                    <td class="text-end">{{ number_format($voucher->total_amount, 2) }} บาท</td>
                                </tr>
                                @if ($voucher->withholding_tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end text-danger">
                                            หัก ณ ที่จ่าย
                                            @if ($voucher->withholding_tax_rate > 0)
                                                ({{ __($voucher->withholding_tax_rate) }}%)
                                            @else
                                            @endif
                                        </td>
                                        <td class="text-end text-danger">
                                            -{{ number_format($voucher->withholding_tax_amount, 2) }} บาท
                                        </td>
                                    </tr>
                                    <tr class="total-row" style="background-color: #d1e7dd;">
                                        <td colspan="3" class="text-end">ยอดสุทธิ</td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($voucher->net_amount, 2) }} บาท
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
            @if ($voucher->attachments && $voucher->attachments->count() > 0)
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa fa-paperclip"></i> เอกสารแนบใบสำคัญจ่าย</h6>
                    </div>
                    <div class="card-body">
                        <div class="attachment-gallery">
                            @foreach ($voucher->attachments as $attachment)
                                <div class="attachment-card">
                                    @if ($attachment->isImage())
                                        <img src="{{ Storage::url($attachment->file_path) }}"
                                            alt="{{ $attachment->original_name }}"
                                            onclick="openLightbox('{{ Storage::url($attachment->file_path) }}')">
                                    @else
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                            <div class="file-icon">
                                                <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                            </div>
                                        </a>
                                    @endif
                                    <div class="file-name">{{ $attachment->original_name }}</div>
                                    <small class="text-muted">{{ $attachment->file_size_formatted }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12">
            <!-- Approval Actions -->
            @if ($voucher->canReview() || $voucher->canApprove() || $voucher->canComplete())
                <div class="main-card mb-3 card no-print">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-check-circle"></i> ดำเนินการ</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                // ใช้ระบบ permission แทน level
                                $canReview = Auth::user()->can('payment-voucher.review');
                                $canApprove = Auth::user()->can('payment-voucher.approve');
                                $disableAccount = $canReview ? '' : 'disabled';
                                $enableFinance = $canApprove ? '' : 'disabled';
                            @endphp
                            @if ($voucher->canReview())
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card" style="border-color: #17a2b8; background: #f0fafc;">
                                        <h6><i class="fa fa-search text-info"></i> ตรวจสอบใบสำคัญจ่าย</h6>
                                        <form action="{{ route('payment-voucher.review', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                                                <textarea name="remark" class="form-control" rows="2"></textarea>
                                            </div>
                                            @can('payment-voucher.test')
                                                <div class="mb-3 form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_test"
                                                        id="is_test_review" value="1">
                                                    <label class="form-check-label text-danger fw-bold" for="is_test_review">
                                                        ทดสอบระบบ (Test Mode)
                                                    </label>
                                                </div>
                                            @endcan
                                            <button type="submit" class="btn btn-info" {{ $disableAccount }}>
                                                <i class="fa fa-check"></i> ตรวจสอบเรียบร้อย
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if ($voucher->canApprove())
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card">
                                        <h6><i class="fa fa-thumbs-up text-success"></i> อนุมัติใบสำคัญจ่าย</h6>
                                        <form action="{{ route('payment-voucher.approve', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">หมายเหตุ (ถ้ามี)</label>
                                                <textarea name="remark" class="form-control" rows="2"></textarea>
                                            </div>
                                            @can('payment-voucher.test')
                                                <div class="mb-3 form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_test"
                                                        id="is_test_approve" value="1">
                                                    <label class="form-check-label text-danger fw-bold" for="is_test_approve">
                                                        ทดสอบระบบ (Test Mode)
                                                    </label>
                                                </div>
                                            @endcan
                                            <button type="submit" class="btn btn-success" {{ $enableFinance }}>
                                                <i class="fa fa-check"></i> อนุมัติ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card" style="border-color: #dc3545; background: #fff8f8;">
                                        <h6><i class="fa fa-thumbs-down text-danger"></i> ปฏิเสธใบสำคัญจ่าย</h6>
                                        <form action="{{ route('payment-voucher.reject', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">เหตุผลที่ปฏิเสธ <span
                                                        class="text-danger">*</span></label>
                                                <textarea name="remark" class="form-control" rows="2" required></textarea>
                                            </div>
                                            @can('payment-voucher.test')
                                                <div class="mb-3 form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_test"
                                                        id="is_test_reject" value="1">
                                                    <label class="form-check-label text-danger fw-bold" for="is_test_reject">
                                                        ทดสอบระบบ (Test Mode)
                                                    </label>
                                                </div>
                                            @endcan
                                            <button type="submit" class="btn btn-danger" {{ $enableFinance }}>
                                                <i class="fa fa-times"></i> ปฏิเสธ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if ($voucher->canReview())
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card" style="border-color: #fd7e14; background: #fff8f3;">
                                        <h6><i class="fa fa-reply text-warning"></i> ส่งกลับแก้ไข</h6>
                                        <form action="{{ route('payment-voucher.reject-to-draft', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">เหตุผล <span
                                                        class="text-danger">*</span></label>
                                                <textarea name="remark" class="form-control" rows="2" required placeholder="ระบุสิ่งที่ต้องแก้ไข..."></textarea>
                                            </div>
                                            @can('payment-voucher.test')
                                                <div class="mb-3 form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_test"
                                                        id="is_test_reject_draft" value="1">
                                                    <label class="form-check-label text-danger fw-bold"
                                                        for="is_test_reject_draft">
                                                        ทดสอบระบบ (Test Mode)
                                                    </label>
                                                </div>
                                            @endcan
                                            <button type="submit" class="btn btn-warning" {{ $disableAccount }}>
                                                <i class="fa fa-reply"></i> ส่งกลับแก้ไข
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if ($voucher->canComplete())
                                <div class="col-md-6 mb-3">
                                    <div class="approval-card">
                                        <h6><i class="fa fa-flag-checkered text-success"></i> บันทึกเสร็จสิ้น</h6>
                                        <form action="{{ route('payment-voucher.complete', $voucher->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">ชื่อผู้จ่ายเงิน <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="payer_name" class="form-control" required
                                                    value="{{ Auth::user()->name }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">วันที่จ่ายเงิน</label>
                                                <input type="date" name="paid_date" class="form-control"
                                                    value="{{ date('Y-m-d') }}">
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
                                        @case('create')
                                            <span class="text-primary">สร้างเอกสาร</span>
                                        @break

                                        @case('submit')
                                            <span class="text-warning">ส่งตรวจสอบ</span>
                                        @break

                                        @case('review')
                                            <span class="text-info">ตรวจสอบแล้ว</span>
                                        @break

                                        @case('approve')
                                            <span class="text-success">อนุมัติ</span>
                                        @break

                                        @case('reject')
                                            <span class="text-danger">ปฏิเสธ</span>
                                        @break

                                        @case('reject_to_draft')
                                            <span class="text-warning">ส่งกลับแก้ไข</span>
                                        @break

                                        @case('complete')
                                            <span class="text-success">เสร็จสิ้น</span>
                                        @break

                                        @case('update')
                                            <span class="text-secondary">แก้ไข</span>
                                        @break

                                        @default
                                            <span>{{ $log->action }}</span>
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

        @if ($voucher->status === 'completed')
            <div class="col-12">
                <!-- Payment Info (if completed) -->
                <div class="main-card mb-3 card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-check-circle"></i> ข้อมูลการจ่ายเงิน</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">ผู้จ่ายเงิน</div>
                                <div class="info-value">{{ $voucher->payer_name ?? $voucher->receiver_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">วันที่จ่ายเงิน</div>
                                <div class="info-value">
                                    {{ $voucher->paid_date ? \Carbon\Carbon::parse($voucher->paid_date)->format('d/m/Y') : ($voucher->received_date ? \Carbon\Carbon::parse($voucher->received_date)->format('d/m/Y') : '-') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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

        // Lightbox functionality
        function openLightbox(imageSrc) {
            let lightbox = document.getElementById('lightbox');
            if (!lightbox) {
                lightbox = document.createElement('div');
                lightbox.id = 'lightbox';
                lightbox.className = 'lightbox';
                lightbox.innerHTML =
                    '<span class="close" onclick="closeLightbox()">&times;</span><img src="" id="lightbox-img">';
                lightbox.onclick = function(e) {
                    if (e.target === lightbox) closeLightbox();
                };
                document.body.appendChild(lightbox);
            }
            document.getElementById('lightbox-img').src = imageSrc;
            lightbox.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
@endsection
