@extends('layouts.main_template')

@section('style')
    <style>
        .preview-card {
            border-left: 5px solid #17a2b8;
        }

        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-check icon-gradient bg-info"></i>
                    </div>
                    <div>
                        ตรวจสอบข้อมูล (Preview)
                        <div class="page-title-subheading">ตรวจสอบความถูกต้องและแก้ไขข้อมูลก่อนนำเข้า</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('payment-voucher.import') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> ยกเลิก / นำเข้าใหม่
                    </a>
                </div>
            </div>
        </div>

        @if (!empty($previewResult['warnings']))
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                    <h5 class="alert-heading font-weight-bold"><i class="fa fa-exclamation-triangle"></i> คำเตือน</h5>
                    <ul class="mb-0 ps-3">
                        @foreach ($previewResult['warnings'] as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="col-12">
            <form action="{{ route('payment-voucher.import.store') }}" method="POST">
                @csrf
                <input type="hidden" name="branch_code" value="{{ $data['branch_code'] }}">

                <div class="main-card mb-3 card preview-card shadow-sm">
                    <div class="card-header text-white bg-primary">ข้อมูลหลัก</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">เลขที่ใบสำคัญจ่าย <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="voucher_no" class="form-control form-control-lg"
                                    value="{{ $data['generated_voucher_no'] }}" required>
                                @if ($data['excel_voucher_no'])
                                    <div class="text-muted mt-1 small"><i class="fa fa-file-text-o"></i> เลขที่ในไฟล์:
                                        {{ $data['excel_voucher_no'] }}</div>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">วันที่ <span class="text-danger">*</span></label>
                                <input type="text" name="voucher_date" class="form-control form-control-lg datepicker"
                                    value="{{ $data['voucher_date'] }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label font-weight-bold">จ่ายให้ (Pay To) <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="pay_to" class="form-control" value="{{ $data['pay_to'] }}"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea name="pay_to_address" class="form-control" rows="2">{{ $data['pay_to_address'] }}</textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ธนาคาร</label>
                                <input type="text" name="pay_to_bank_name" class="form-control"
                                    value="{{ $data['pay_to_bank_name'] }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เลขที่บัญชี</label>
                                <input type="text" name="pay_to_account_no" class="form-control"
                                    value="{{ $data['pay_to_account_no'] }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="main-card mb-3 card shadow-sm">
                    <div class="card-header bg-light">รายการ</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="80" class="text-center">ลำดับ</th>
                                        <th>รายการ</th>
                                        <th width="200" class="text-end">จำนวนเงิน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['items'] as $index => $item)
                                        <tr>
                                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][description]"
                                                    class="form-control border-0 bg-transparent"
                                                    value="{{ $item['description'] }}" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    name="items[{{ $index }}][amount]"
                                                    class="form-control text-end border-0 bg-transparent font-weight-bold"
                                                    value="{{ $item['amount'] }}" required>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="2" class="text-end font-weight-bold">รวมทั้งสิ้น</td>
                                        <td class="text-end font-weight-bold text-primary h5">
                                            {{ number_format($data['total_amount'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-5">
                    <button type="submit" class="btn btn-success btn-lg shadow">
                        <i class="fa fa-save"></i> ยืนยันการนำเข้าข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
