@extends('layouts.main_template')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .item-row, .deduction-row { 
            background: #ffffff; 
            padding: 20px; 
            border-radius: 12px; 
            margin-bottom: 15px; 
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .item-row:hover, .deduction-row:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #bbdefb;
        }
        .required-mark { color: #f44336; font-weight: bold; }
        .total-section { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 25px; 
            border-radius: 15px; 
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .total-amount { 
            font-size: 2rem; 
            font-weight: 800; 
            color: #1a237e;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .section-header {
            border-bottom: 2px solid #673ab7;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #4527a0;
            font-weight: 700;
        }
        .attachment-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        .attachment-item {
            position: relative;
            border: 1px solid #e0e0e0;
            padding: 8px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            width: 100px;
        }
        .attachment-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
        }
        .attachment-item .file-icon {
            width: 100%;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            border-radius: 6px;
            color: #d32f2f;
        }
        .existing-attachment {
            display: flex;
            align-items: center;
            background: #f0f4f8;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #4527a0;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-note2 icon-gradient bg-plum-plate"></i>
                    </div>
                    <div>
                        แก้ไขใบสำคัญรับ
                        <div class="page-title-subheading">แก้ไขใบสำคัญรับเลขที่ {{ $voucher->voucher_no }}</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('receipt-voucher.show', $voucher->id) }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="main-card mb-3 card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('receipt-voucher.update', $voucher->id) }}" method="POST" enctype="multipart/form-data" id="voucherForm" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="section-header"><i class="fa fa-file-text"></i> ข้อมูลหลัก</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">วันที่ <span class="required-mark">*</span></label>
                                        <input type="date" name="voucher_date" class="form-control" value="{{ $voucher->voucher_date->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">เลขที่ใบสำคัญรับ</label>
                                        <input type="text" class="form-control bg-light" id="voucherNoPreview" value="{{ $voucher->voucher_no }}" readonly disabled>
                                        @if($voucher->status == \App\Models\ReceiptVoucher::STATUS_DRAFT)
                                            <small class="text-muted">รูปแบบ: รหัสสาขา + ปี + เดือน + ลำดับ (จะเปลี่ยนหากเปลี่ยนสาขาในสถานะร่าง)</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">สาขา / แผนก <span class="required-mark">*</span></label>
                                    <select name="branch_code" id="branchCode" class="form-select select2-basic" required>
                                        @foreach($branchCodes as $code => $name)
                                            <option value="{{ $code }}" {{ $voucher->branch_code == $code ? 'selected' : '' }}>{{ $name }} ({{ $code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="section-header"><i class="fa fa-user"></i> ข้อมูลผู้รับเงิน / รับจาก</h5>
                                <div class="mb-3">
                                    <label class="form-label">รับจาก (ชื่อ/บริษัท) <span class="required-mark">*</span></label>
                                    <select name="receive_from" id="receiveFromSelect" class="form-control" required>
                                        <option value="{{ $voucher->receive_from }}" selected>{{ $voucher->receive_from }}</option>
                                    </select>
                                    <input type="hidden" name="payee_id" id="payeeId" value="{{ $voucher->payee_id }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ที่อยู่</label>
                                    <textarea name="receive_from_address" id="receiveFromAddress" class="form-control" rows="2" placeholder="ที่อยู่ผู้รับเงิน">{{ $voucher->receive_from_address }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">เลขที่บัญชี</label>
                                        <input type="text" name="receive_from_account_no" id="receiveFromAccountNo" class="form-control" value="{{ $voucher->receive_from_account_no }}" placeholder="เช่น 123-4-56789-0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ธนาคาร</label>
                                        <select name="receive_from_bank_name" id="receiveFromBankName" class="form-select">
                                            <option value="">-- เลือกธนาคาร --</option>
                                            @php
                                                $banks = ['ธนาคารกสิกรไทย', 'ธนาคารไทยพาณิชย์', 'ธนาคารกรุงเทพ', 'ธนาคารกรุงไทย', 'ธนาคารกรุงศรีอยุธยา', 'ธนาคารทหารไทยธนชาต', 'ธนาคารออมสิน', 'ธนาคาร ธ.ก.ส.', 'ธนาคารอาคารสงเคราะห์', 'ธนาคารซีไอเอ็มบี ไทย', 'ธนาคารยูโอบี', 'ธนาคารแลนด์ แอนด์ เฮ้าส์', 'ธนาคารเกียรตินาคินภัทร', 'เงินสด', 'อื่นๆ'];
                                            @endphp
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank }}" {{ $voucher->receive_from_bank_name == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5 class="section-header"><i class="fa fa-list"></i> รายการรับ <span class="required-mark">*</span></h5>
                            <div id="itemsContainer">
                                @forelse($voucher->receiptItems as $index => $item)
                                    <div class="item-row">
                                        <div class="row align-items-end">
                                            <div class="col-md-1 mb-2">
                                                <label class="form-label text-muted small">ลำดับ</label>
                                                <input type="text" class="form-control text-center bg-light item-no" value="{{ $index + 1 }}" readonly>
                                            </div>
                                            <div class="col-md-8 mb-2">
                                                <label class="form-label text-muted small">รายละเอียด <span class="required-mark">*</span></label>
                                                <input type="text" name="items[{{ $index }}][description]" class="form-control" value="{{ $item->description }}" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label text-muted small">จำนวนเงิน <span class="required-mark">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="items[{{ $index }}][amount]" class="form-control item-amount text-end" value="{{ $item->amount }}" step="0.01" min="0" required placeholder="0.00">
                                                    <span class="input-group-text">บาท</span>
                                                </div>
                                            </div>
                                            <div class="col-md-1 mb-2 text-end">
                                                <button type="button" class="btn btn-outline-danger w-100 btn-remove-item" onclick="removeItem(this)" {{ $voucher->receiptItems->count() <= 1 ? 'disabled' : '' }}>
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="item-row">
                                        <div class="row align-items-end">
                                            <div class="col-md-1 mb-2">
                                                <input type="text" class="form-control text-center bg-light item-no" value="1" readonly>
                                            </div>
                                            <div class="col-md-8 mb-2">
                                                <input type="text" name="items[0][description]" class="form-control" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <div class="input-group">
                                                    <input type="number" name="items[0][amount]" class="form-control item-amount text-end" step="0.01" min="0" required>
                                                    <span class="input-group-text">บาท</span>
                                                </div>
                                            </div>
                                            <div class="col-md-1 mb-2">
                                                <button type="button" class="btn btn-outline-danger w-100 btn-remove-item" onclick="removeItem(this)" disabled><i class="fa fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-4 radius-round px-4" onclick="addItem()">
                                <i class="fa fa-plus"></i> เพิ่มรายการรับ
                            </button>
                        </div>

                        <div class="mt-2">
                            <h5 class="section-header text-danger"><i class="fa fa-minus-circle"></i> รายการหัก (ถ้ามี)</h5>
                            <div id="deductionsContainer">
                                @foreach($voucher->deductions as $index => $item)
                                    <div class="deduction-row">
                                        <div class="row align-items-end">
                                            <div class="col-md-1 mb-2">
                                                <input type="text" class="form-control text-center bg-light deduction-no" value="{{ $index + 1 }}" readonly>
                                            </div>
                                            <div class="col-md-8 mb-2">
                                                <input type="text" name="deductions[{{ $index }}][description]" class="form-control" value="{{ $item->description }}" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <div class="input-group">
                                                    <input type="number" name="deductions[{{ $index }}][amount]" class="form-control deduction-amount text-end" value="{{ $item->amount }}" step="0.01" min="0" required>
                                                    <span class="input-group-text text-danger">บาท</span>
                                                </div>
                                            </div>
                                            <div class="col-md-1 mb-2">
                                                <button type="button" class="btn btn-outline-danger w-100" onclick="removeDeduction(this)"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm mb-4 radius-round px-4" onclick="addDeduction()">
                                <i class="fa fa-plus"></i> เพิ่มรายการหัก
                            </button>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-7">
                                <div class="mb-4">
                                    <label class="form-label fw-bold"><i class="fa fa-commenting"></i> หมายเหตุ</label>
                                    <textarea name="remark" class="form-control" rows="3" placeholder="ระบุหมายเหตุเพิ่มเติม">{{ $voucher->remark }}</textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold"><i class="fa fa-paperclip"></i> แนบไฟล์เอกสารเพิ่มเติม</label>
                                    <div class="p-3 border rounded bg-light mb-3">
                                        <input type="file" name="attachments[]" id="attachmentInput" class="form-control" multiple accept="image/*,.pdf">
                                        <div id="attachmentPreview" class="attachment-preview"></div>
                                    </div>
                                    @if($voucher->attachments->count() > 0)
                                        <label class="form-label fw-bold small text-muted">ไฟล์ที่แนบไว้เดิม:</label>
                                        @foreach($voucher->attachments as $att)
                                            <div class="existing-attachment shadow-sm">
                                                <i class="fa {{ $att->isPdf() ? 'fa-file-pdf-o text-danger' : ($att->isImage() ? 'fa-image text-primary' : 'fa-file-o') }} fa-lg me-3"></i>
                                                <span class="flex-grow-1 text-truncate">{{ $att->original_name }}</span>
                                                <small class="text-muted mx-3">{{ $att->file_size_formatted }}</small>
                                                <a href="{{ $att->file_url }}" target="_blank" class="btn btn-sm btn-link"><i class="fa fa-eye"></i> ดูไฟล์</a>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="total-section">
                                    <div class="d-flex justify-content-between mb-3 h6">
                                        <span class="text-muted">ยอดรวมรับ:</span>
                                        <span id="subTotalDisplay" class="fw-bold text-dark">{{ number_format($voucher->total_amount, 2) }} บาท</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 h6 text-danger">
                                        <span>ยอดรวมหัก:</span>
                                        <span id="deductionTotalDisplay" class="fw-bold">-{{ number_format($voucher->total_deduction_amount, 2) }} บาท</span>
                                    </div>
                                    <hr style="border-top: 2px dashed #99aab5;">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="h5 mb-0 fw-bold">จำนวนเงินสุทธิ:</span>
                                        <div class="total-amount" id="netTotalDisplay">{{ number_format($voucher->net_amount, 2) }} บาท</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5 border-top pt-4">
                            <button type="submit" name="submit" value="0" class="btn btn-lg btn-outline-secondary px-5 me-3 shadow-sm">
                                <i class="fa fa-save"></i> บันทึกการแก้ไข
                            </button>
                            <button type="submit" name="submit" value="1" class="btn btn-lg btn-primary px-5 shadow-sm">
                                <i class="fa fa-paper-plane"></i> บันทึกและส่งตรวจสอบ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let itemIndex = {{ $voucher->receiptItems->count() ?: 1 }};
        let deductionIndex = {{ $voucher->deductions->count() ?: 0 }};

        $(document).ready(function() {
            $('.select2-basic').select2();

            $('#receiveFromSelect').select2({
                placeholder: 'พิมพ์ชื่อผู้จ่ายเงินหรือบริษัท...',
                allowClear: true,
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                },
                ajax: {
                    url: '{{ route('receipt-voucher.search-payee') }}',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.name,
                                    text: item.name + (item.account_no ? ' (' + item.account_no + ')' : ''),
                                    payee: item
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });

            $('#receiveFromSelect').on('select2:select', function(e) {
                var data = e.params.data;
                if (data.payee) {
                    $('#payeeId').val(data.payee.id);
                    $('#receiveFromAddress').val(data.payee.address || '');
                    $('#receiveFromAccountNo').val(data.payee.account_no || '');
                    if (data.payee.bank_name) {
                        $('#receiveFromBankName').val(data.payee.bank_name);
                    }
                } else {
                    $('#payeeId').val('');
                    // Clear other fields if it's a new tag
                    if (data.newTag) {
                        $('#receiveFromAddress').val('');
                        $('#receiveFromAccountNo').val('');
                        $('#receiveFromBankName').val('');
                    }
                }
            });

            // Attachment Preview
            $('#attachmentInput').on('change', function(e) {
                const preview = $('#attachmentPreview');
                preview.empty();
                Array.from(e.target.files).forEach(file => {
                    const item = $('<div class="attachment-item"></div>');
                    if (file.type.startsWith('image/')) {
                        const img = $('<img>').attr('src', URL.createObjectURL(file));
                        item.append(img);
                    } else {
                        const icon = $('<div class="file-icon"><i class="fa fa-file-pdf-o fa-2x"></i></div>');
                        item.append(icon);
                    }
                    const name = $('<small class="d-block text-center text-truncate mt-1"></small>').text(file.name);
                    item.append(name);
                    preview.append(item);
                });
            });
        });

        function addItem() {
            const html = `
                <div class="item-row">
                    <div class="row align-items-end">
                        <div class="col-md-1 mb-2">
                            <input type="text" class="form-control text-center bg-light item-no" value="${$('.item-row').length + 1}" readonly>
                        </div>
                        <div class="col-md-8 mb-2">
                            <input type="text" name="items[${itemIndex}][description]" class="form-control" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="input-group">
                                <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount text-end" step="0.01" min="0" required placeholder="0.00">
                                <span class="input-group-text">บาท</span>
                            </div>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button type="button" class="btn btn-outline-danger w-100 btn-remove-item" onclick="removeItem(this)"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $('#itemsContainer').append(html);
            itemIndex++;
            updateNumbers('item-no');
            updateRemoveButtons();
            calculateTotal();
        }

        function addDeduction() {
            const html = `
                <div class="deduction-row">
                    <div class="row align-items-end">
                        <div class="col-md-1 mb-2">
                            <input type="text" class="form-control text-center bg-light deduction-no" value="${$('.deduction-row').length + 1}" readonly>
                        </div>
                        <div class="col-md-8 mb-2">
                            <input type="text" name="deductions[${deductionIndex}][description]" class="form-control" required placeholder="ระบุรายการหัก">
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="input-group">
                                <input type="number" name="deductions[${deductionIndex}][amount]" class="form-control deduction-amount text-end" step="0.01" min="0" required placeholder="0.00">
                                <span class="input-group-text text-danger">บาท</span>
                            </div>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeDeduction(this)"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $('#deductionsContainer').append(html);
            deductionIndex++;
            updateNumbers('deduction-no');
            calculateTotal();
        }

        function removeItem(btn) {
            $(btn).closest('.item-row').remove();
            updateNumbers('item-no');
            updateRemoveButtons();
            calculateTotal();
        }

        function removeDeduction(btn) {
            $(btn).closest('.deduction-row').remove();
            updateNumbers('deduction-no');
            calculateTotal();
        }

        function updateNumbers(className) {
            $('.' + className).each((i, input) => $(input).val(i + 1));
        }

        function updateRemoveButtons() {
            const buttons = $('.btn-remove-item');
            buttons.prop('disabled', buttons.length === 1);
        }

        function calculateTotal() {
            let total = 0;
            $('.item-amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#subTotalDisplay').text(total.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท');

            let deductionTotal = 0;
            $('.deduction-amount').each(function() {
                deductionTotal += parseFloat($(this).val()) || 0;
            });
            $('#deductionTotalDisplay').text('-' + deductionTotal.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท');

            const netTotal = total - deductionTotal;
            $('#netTotalDisplay').text(netTotal.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท');
        }

        $(document).on('input', '.item-amount, .deduction-amount', function() {
            calculateTotal();
        });

        // Form validation — ตรวจสอบทุก field ก่อน submit พร้อมแสดง alert แดง
        document.getElementById('voucherForm').addEventListener('submit', function(e) {
            let errors = [];
            let firstErrorField = null;

            // ล้าง error เดิมทั้งหมด
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            const existingAlert = document.querySelector('#validationAlert');
            if (existingAlert) existingAlert.remove();

            // 1. ตรวจสอบวันที่
            const voucherDate = document.querySelector('input[name="voucher_date"]');
            if (voucherDate && !voucherDate.value) {
                errors.push('กรุณาระบุวันที่');
                voucherDate.classList.add('is-invalid');
                if (!firstErrorField) firstErrorField = voucherDate;
            }

            // 2. ตรวจสอบสาขา
            const branchCode = document.getElementById('branchCode');
            if (branchCode && !branchCode.value) {
                errors.push('กรุณาเลือกสาขา');
                branchCode.classList.add('is-invalid');
                if (!firstErrorField) firstErrorField = branchCode;
            }

            // 3. ตรวจสอบผู้จ่ายเงิน (รับจาก)
            const receiveFrom = document.getElementById('receiveFromSelect');
            if (receiveFrom && !receiveFrom.value) {
                errors.push('กรุณาระบุชื่อผู้จ่ายเงิน (รับจาก)');
                if (!firstErrorField) firstErrorField = receiveFrom;
            }

            // 4. ตรวจสอบรายการรับ
            const items = document.querySelectorAll('.item-row');
            let hasValidItem = false;

            items.forEach((item, index) => {
                const description = item.querySelector('input[name*="[description]"]');
                const amount = item.querySelector('.item-amount');

                if (description && !description.value.trim()) {
                    errors.push(`รายการที่ ${index + 1}: กรุณาระบุรายละเอียด`);
                    description.classList.add('is-invalid');
                    if (!firstErrorField) firstErrorField = description;
                }

                if (amount) {
                    const val = parseFloat(amount.value);
                    if (!amount.value || isNaN(val) || val <= 0) {
                        errors.push(`รายการที่ ${index + 1}: กรุณาระบุจำนวนเงินให้ถูกต้อง`);
                        amount.classList.add('is-invalid');
                        if (!firstErrorField) firstErrorField = amount;
                    } else {
                        hasValidItem = true;
                    }
                }
            });

            if (!hasValidItem && items.length > 0) {
                errors.push('กรุณาเพิ่มรายการรับอย่างน้อย 1 รายการที่มีจำนวนเงินมากกว่า 0');
            }

            // แสดง error alert แดงด้านบน
            if (errors.length > 0) {
                e.preventDefault();
                e.stopPropagation();

                let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" id="validationAlert" role="alert">';
                errorHtml += '<h5 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> พบข้อผิดพลาด ' + errors.length + ' รายการ:</h5>';
                errorHtml += '<ul class="mb-0">';
                errors.forEach(err => {
                    errorHtml += `<li>${err}</li>`;
                });
                errorHtml += '</ul>';
                errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                errorHtml += '</div>';

                const mainCard = document.querySelector('.main-card');
                if (mainCard) {
                    mainCard.insertAdjacentHTML('afterbegin', errorHtml);
                }

                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => firstErrorField.focus(), 500);
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });

        // ลบ error styling เมื่อผู้ใช้แก้ไขข้อมูล
        document.body.addEventListener('input', function(e) {
            if (e.target.classList.contains('form-control') || e.target.classList.contains('form-select')) {
                e.target.classList.remove('is-invalid');
            }
        });
    </script>
@endsection
