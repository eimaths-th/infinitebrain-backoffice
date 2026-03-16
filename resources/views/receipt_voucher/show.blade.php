@extends('layouts.main_template')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .voucher-header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .section-title { background: #f4f4f4; padding: 5px 10px; font-weight: bold; margin-top: 20px; border-left: 4px solid #007bff; }
        .items-table th { background: #eee; }
        .total-row { font-weight: bold; background: #fafafa; }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .attachment-card { border: 1px solid #ddd; border-radius: 5px; padding: 10px; margin-bottom: 10px; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center voucher-header">
                        <div>
                            <h2 class="mb-0">ใบสำคัญรับ (Receipt Voucher)</h2>
                            <h4 class="text-muted">เลขที่: {{ $voucher->voucher_no }}</h4>
                        </div>
                        <div class="text-end">
                            <span class="status-badge bg-{{ $voucher->status_color }} text-white">
                                {{ $voucher->status_label }}
                            </span>
                            <div class="mt-2">
                                <a href="{{ route('receipt-voucher.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> กลับ
                                </a>
                                <a href="{{ route('receipt-voucher.print-pdf', $voucher->id) }}" target="_blank" class="btn btn-primary">
                                    <i class="fa fa-print"></i> พิมพ์
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>วันที่:</strong> {{ $voucher->voucher_date->format('d/m/Y') }}</p>
                            <p><strong>รับจาก:</strong> {{ $voucher->receive_from }}</p>
                            <p><strong>ที่อยู่:</strong> {{ $voucher->receive_from_address ?: '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ธนาคาร:</strong> {{ $voucher->receive_from_bank_name ?: '-' }}</p>
                            <p><strong>เลขที่บัญชี:</strong> {{ $voucher->receive_from_account_no ?: '-' }}</p>
                            <p><strong>สาขา:</strong> {{ $voucher->branch_code ?: '-' }}</p>
                        </div>
                    </div>

                    <div class="section-title">รายการรับ</div>
                    <table class="table table-bordered items-table mt-2">
                        <thead>
                            <tr>
                                <th width="10%" class="text-center">ลำดับ</th>
                                <th>รายการ</th>
                                <th width="20%" class="text-end">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($voucher->receiptItems as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="2" class="text-end">รวมเงิน</td>
                                <td class="text-end">{{ number_format($voucher->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if($voucher->deductions->count() > 0)
                        <div class="section-title">รายการหัก</div>
                        <table class="table table-bordered items-table mt-2">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">ลำดับ</th>
                                    <th>รายการหัก</th>
                                    <th width="20%" class="text-end">จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($voucher->deductions as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-end text-danger">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td colspan="2" class="text-end">รวมรายการหัก</td>
                                    <td class="text-end text-danger">{{ number_format($voucher->total_deduction_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">จำนวนเงินสุทธิ (Net Amount)</h4>
                                <h3 class="mb-0 font-weight-bold">{{ number_format($voucher->net_amount, 2) }} บาท</h3>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">หมายเหตุ / ข้อมูลเพิ่มเติม</div>
                    <div class="p-2 border rounded mt-2">
                        {{ $voucher->remark ?: 'ไม่มีหมายเหตุ' }}
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="section-title">ไฟล์แนบ</div>
                            <div class="mt-2">
                                @forelse($voucher->attachments as $file)
                                    <div class="attachment-card d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa fa-file"></i> {{ $file->original_name }}
                                            <small class="text-muted d-block">อัปโหลดโดย: {{ $file->uploaded_by_name }}</small>
                                        </div>
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn btn-sm btn-info">เปิด</a>
                                    </div>
                                @empty
                                    <p class="text-muted">ไม่มีไฟล์แนบ</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="section-title">ประวัติการดำเนินการ (Logs)</div>
                            <div class="mt-2">
                                <ul class="list-group list-group-flush">
                                    @foreach($voucher->logs as $log)
                                        <li class="list-group-item p-2">
                                            <small class="text-primary">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                            <strong>{{ $log->user_name }}</strong>: {{ $log->action }}
                                            @if($log->remark)
                                                <small class="d-block text-muted">หมายเหตุ: {{ $log->remark }}</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="workflow-actions text-center p-3">
                        @if($voucher->canVerify())
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#verifyModal">
                                <i class="fa fa-check"></i> ตรวจสอบแล้ว (Verify)
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fa fa-times"></i> ปฏิเสธ (Reject)
                            </button>
                        @endif

                        @if($voucher->canComplete())
                            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#completeModal">
                                <i class="fa fa-flag-checkered"></i> ดำเนินการเสร็จสิ้น (Complete)
                            </button>
                        @endif

                        @if($voucher->status === \App\Models\ReceiptVoucher::STATUS_COMPLETED)
                            <form action="{{ route('receipt-voucher.undo-complete', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการยกเลิกสถานะเสร็จสิ้น?')">
                                @csrf
                                <button type="submit" class="btn btn-warning">ยกเลิกสถานะเสร็จสิ้น</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('receipt-voucher.verify', $voucher->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white"><h5>ตรวจสอบใบสำคัญรับ</h5></div>
                    <div class="modal-body">
                        <label>หมายเหตุการตรวจสอบ</label>
                        <textarea name="remark" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">ยืนยันการตรวจสอบ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('receipt-voucher.reject', $voucher->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white"><h5>ปฏิเสธใบสำคัญรับ</h5></div>
                    <div class="modal-body">
                        <label>เหตุผลที่ปฏิเสธ (จำเป็น)</label>
                        <textarea name="remark" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ยืนยันการปฏิเสธ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('receipt-voucher.complete', $voucher->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white"><h5>ดำเนินการเสร็จสิ้น</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>ชื่อผู้รับเงิน / ผู้ดำเนินการภายนอก</label>
                            <input type="text" name="receiver_name" class="form-control" placeholder="เช่น นาย A">
                        </div>
                        <div class="mb-3">
                            <label>วันที่ดำเนินการเสร็จสิ้น</label>
                            <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <label>หมายเหตุเพิ่มเติม</label>
                        <textarea name="remark" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success">ยืนยันเสร็จสิ้น</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
