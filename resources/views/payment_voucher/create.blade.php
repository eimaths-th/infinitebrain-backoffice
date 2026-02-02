@extends('layouts.main_template')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .item-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
        }

        .btn-remove-item {
            padding: 5px 10px;
        }

        .total-section {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #0d6efd;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .required-mark {
            color: red;
        }

        .attachment-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #ffc107;
            margin-top: 10px;
        }

        .attachment-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .attachment-item {
            position: relative;
            border: 1px solid #dee2e6;
            padding: 5px;
            border-radius: 5px;
            background: white;
        }

        .attachment-item img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
        }

        .attachment-item .file-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .item-attachments {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
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

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-note2 icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        สร้างใบสำคัญจ่าย
                        <div class="page-title-subheading">กรอกข้อมูลเพื่อสร้างใบสำคัญจ่ายใหม่</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('payment-voucher.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <form action="{{ route('payment-voucher.store') }}" method="POST" id="voucherForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fa fa-file-text-o"></i> ข้อมูลใบสำคัญจ่าย
                            </h5>

                            <div class="mb-3">
                                <label class="form-label">สาขา <span class="required-mark">*</span></label>
                                <select name="branch_code" id="branchCode" class="form-select" required>
                                    @foreach ($branchCodes as $code => $name)
                                        <option value="{{ $code }}"
                                            {{ old('branch_code', 'EL') == $code ? 'selected' : '' }}>{{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">เลขที่ใบสำคัญจ่าย (ตัวอย่าง)</label>
                                <input type="text" class="form-control" id="voucherNoPreview" value="{{ $voucherNo }}"
                                    readonly disabled>
                                <small class="text-muted">รูปแบบ: รหัสสาขา + ปี + เดือน + ลำดับ (เช่น EL20260101)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">วันที่ <span class="required-mark">*</span></label>
                                <input type="text" name="voucher_date" class="form-control datepicker"
                                    value="{{ old('voucher_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fa fa-user"></i> ข้อมูลผู้รับเงิน
                            </h5>

                            <div class="mb-3">
                                <label class="form-label">จ่ายให้ (ชื่อ/บริษัท) <span class="required-mark">*</span></label>
                                <select name="pay_to" id="payToSelect" class="form-select" required>
                                    <option value="">-- พิมพ์เพื่อค้นหาหรือเพิ่มใหม่ --</option>
                                </select>
                                <small class="text-muted">พิมพ์ชื่อเพื่อค้นหาจากรายการเดิม
                                    หรือพิมพ์ชื่อใหม่เพื่อเพิ่ม</small>
                                <input type="hidden" name="payee_id" id="payeeId" value="">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea name="pay_to_address" id="payToAddress" class="form-control" rows="2" placeholder="ที่อยู่ผู้รับเงิน">{{ old('pay_to_address') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">เลขที่บัญชีธนาคาร</label>
                                    <input type="text" name="pay_to_account_no" id="payToAccountNo" class="form-control"
                                        value="{{ old('pay_to_account_no') }}" placeholder="เช่น 123-4-56789-0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ชื่อธนาคาร</label>
                                    <select name="pay_to_bank_name" id="payToBankName" class="form-select">
                                        <option value="">-- เลือกธนาคาร --</option>
                                        <option value="ธนาคารกสิกรไทย"
                                            {{ old('pay_to_bank_name') == 'ธนาคารกสิกรไทย' ? 'selected' : '' }}>
                                            ธนาคารกสิกรไทย</option>
                                        <option value="ธนาคารไทยพาณิชย์"
                                            {{ old('pay_to_bank_name') == 'ธนาคารไทยพาณิชย์' ? 'selected' : '' }}>
                                            ธนาคารไทยพาณิชย์</option>
                                        <option value="ธนาคารกรุงเทพ"
                                            {{ old('pay_to_bank_name') == 'ธนาคารกรุงเทพ' ? 'selected' : '' }}>
                                            ธนาคารกรุงเทพ</option>
                                        <option value="ธนาคารกรุงไทย"
                                            {{ old('pay_to_bank_name') == 'ธนาคารกรุงไทย' ? 'selected' : '' }}>
                                            ธนาคารกรุงไทย</option>
                                        <option value="ธนาคารกรุงศรีอยุธยา"
                                            {{ old('pay_to_bank_name') == 'ธนาคารกรุงศรีอยุธยา' ? 'selected' : '' }}>
                                            ธนาคารกรุงศรีอยุธยา</option>
                                        <option value="ธนาคารทหารไทยธนชาต"
                                            {{ old('pay_to_bank_name') == 'ธนาคารทหารไทยธนชาต' ? 'selected' : '' }}>
                                            ธนาคารทหารไทยธนชาต</option>
                                        <option value="ธนาคารออมสิน"
                                            {{ old('pay_to_bank_name') == 'ธนาคารออมสิน' ? 'selected' : '' }}>ธนาคารออมสิน
                                        </option>
                                        <option value="ธนาคาร ธ.ก.ส."
                                            {{ old('pay_to_bank_name') == 'ธนาคาร ธ.ก.ส.' ? 'selected' : '' }}>ธนาคาร
                                            ธ.ก.ส.</option>
                                        <option value="ธนาคารอาคารสงเคราะห์"
                                            {{ old('pay_to_bank_name') == 'ธนาคารอาคารสงเคราะห์' ? 'selected' : '' }}>
                                            ธนาคารอาคารสงเคราะห์</option>
                                        <option value="ธนาคารซีไอเอ็มบี ไทย"
                                            {{ old('pay_to_bank_name') == 'ธนาคารซีไอเอ็มบี ไทย' ? 'selected' : '' }}>
                                            ธนาคารซีไอเอ็มบี ไทย</option>
                                        <option value="ธนาคารยูโอบี"
                                            {{ old('pay_to_bank_name') == 'ธนาคารยูโอบี' ? 'selected' : '' }}>ธนาคารยูโอบี
                                        </option>
                                        <option value="ธนาคารแลนด์ แอนด์ เฮ้าส์"
                                            {{ old('pay_to_bank_name') == 'ธนาคารแลนด์ แอนด์ เฮ้าส์' ? 'selected' : '' }}>
                                            ธนาคารแลนด์ แอนด์ เฮ้าส์</option>
                                        <option value="ธนาคารเกียรตินาคินภัทร"
                                            {{ old('pay_to_bank_name') == 'ธนาคารเกียรตินาคินภัทร' ? 'selected' : '' }}>
                                            ธนาคารเกียรตินาคินภัทร</option>
                                        <option value="เงินสด"
                                            {{ old('pay_to_bank_name') == 'เงินสด' ? 'selected' : '' }}>
                                            เงินสด</option>
                                        <option value="อื่นๆ" {{ old('pay_to_bank_name') == 'อื่นๆ' ? 'selected' : '' }}>
                                            อื่นๆ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Attachments -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa fa-paperclip"></i> เอกสารแนบใบสำคัญจ่าย
                        </h5>
                        <div class="attachment-section">
                            <label class="form-label">แนบ Slip / เอกสารอ้างอิง (รองรับหลายไฟล์)</label>
                            <input type="file" name="voucher_attachments[]" id="voucherAttachments"
                                class="form-control" multiple accept="image/*,.pdf">
                            <small class="text-muted">รองรับ: JPG, PNG, GIF, PDF (สูงสุด 10MB ต่อไฟล์)</small>
                            <div id="voucherAttachmentPreview" class="attachment-preview"></div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa fa-list"></i> รายการ
                        </h5>

                        <div id="itemsContainer">
                            <div class="item-row" data-index="0">
                                <div class="row">
                                    <div class="col-md-1">
                                        <label class="form-label">ลำดับ</label>
                                        <input type="text" class="form-control text-center item-no" value="1"
                                            readonly>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">รายละเอียด <span class="required-mark">*</span></label>
                                        <input type="text" name="items[0][description]" class="form-control"
                                            placeholder="รายละเอียดการจ่าย" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">จำนวนเงิน <span class="required-mark">*</span></label>
                                        <input type="number" name="items[0][amount]" class="form-control item-amount"
                                            step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Slip/เอกสาร</label>
                                        <input type="file" name="items[0][attachments][]"
                                            class="form-control form-control-sm" multiple accept="image/*,.pdf">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-remove-item"
                                            onclick="removeItem(this)" disabled>
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary mt-2" onclick="addItem()">
                            <i class="fa fa-plus"></i> เพิ่มรายการ
                        </button>
                    </div>

                    <!-- Total Section -->
                    <div class="row mb-4">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea name="remark" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม">{{ old('remark') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="total-section">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">ยอดรวม:</span>
                                    <span id="subTotalDisplay">0.00</span>
                                </div>

                                <div class="d-flex flex-column mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold me-2">หัก ณ ที่จ่าย:</span>
                                            <div class="form-check form-switch ms-2">
                                                <input class="form-check-input" type="checkbox" id="manualTaxSwitch">
                                                <label class="form-check-label small"
                                                    for="manualTaxSwitch">ระบุเอง</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Mode: Auto (Percentage) -->
                                        <div id="taxAutoMode" class="d-flex align-items-center">
                                            <select name="withholding_tax_rate" id="withholdingTaxRate"
                                                class="form-select form-select-sm" style="width: 80px;">
                                                <option value="0">0%</option>
                                                <option value="1">1%</option>
                                                <option value="2">2%</option>
                                                <option value="3">3%</option>
                                                <option value="5">5%</option>
                                            </select>
                                        </div>

                                        <!-- Mode: Manual (Input) -->
                                        <div id="taxManualMode" class="d-none">
                                            <input type="number" id="manualTaxInput"
                                                class="form-control form-control-sm text-end" style="width: 100px;"
                                                placeholder="0.00" step="0.01" min="0">
                                        </div>

                                        <span class="text-danger fw-bold" id="withholdingTaxAmountDisplay">-0.00</span>
                                        <input type="hidden" name="withholding_tax_amount" id="withholdingTaxAmount"
                                            value="0">
                                        <input type="hidden" name="net_amount" id="netAmount" value="0">
                                    </div>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0 h5">ยอดสุทธิที่ต้องจ่าย</label>
                                    <div class="total-amount" id="netTotalDisplay">0.00 บาท</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('payment-voucher.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> ยกเลิก
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary" name="submit" value="">
                                    <i class="fa fa-save"></i> บันทึกร่าง
                                </button>
                                <button type="submit" class="btn btn-success" name="submit" value="1">
                                    <i class="fa fa-send"></i> บันทึกและส่งอนุมัติ
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let itemIndex = 0;

        function addItem() {
            itemIndex++;
            const container = document.getElementById('itemsContainer');
            const itemHtml = `
        <div class="item-row" data-index="${itemIndex}">
            <div class="row">
                <div class="col-md-1">
                    <label class="form-label">ลำดับ</label>
                    <input type="text" class="form-control text-center item-no" value="${itemIndex + 1}" readonly>
                </div>
                <div class="col-md-5">
                    <label class="form-label">รายละเอียด <span class="required-mark">*</span></label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="รายละเอียดการจ่าย" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">จำนวนเงิน <span class="required-mark">*</span></label>
                    <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" step="0.01" min="0" placeholder="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Slip/เอกสาร</label>
                    <input type="file" name="items[${itemIndex}][attachments][]" class="form-control form-control-sm" multiple accept="image/*,.pdf">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-remove-item" onclick="removeItem(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', itemHtml);
            updateItemNumbers();
            updateRemoveButtons();
        }

        function removeItem(btn) {
            const row = btn.closest('.item-row');
            row.remove();
            updateItemNumbers();
            updateRemoveButtons();
            calculateTotal();
        }

        function updateItemNumbers() {
            const rows = document.querySelectorAll('.item-row');
            rows.forEach((row, index) => {
                row.querySelector('.item-no').value = index + 1;
            });
        }

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.item-row');
            const buttons = document.querySelectorAll('.btn-remove-item');
            buttons.forEach((btn, index) => {
                btn.disabled = rows.length <= 1;
            });
        }

        function calculateTotal() {
            const amounts = document.querySelectorAll('.item-amount');
            let total = 0;
            amounts.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });

            // แสดงยอดรวม
            document.getElementById('subTotalDisplay').textContent =
                total.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' บาท';

            // คำนวณหัก ณ ที่จ่าย
            let taxAmount = 0;
            const isManualMode = document.getElementById('manualTaxSwitch').checked;

            if (isManualMode) {
                // โหมดระบุเอง
                taxAmount = parseFloat(document.getElementById('manualTaxInput').value) || 0;
                // อัปเดต dropdown เป็น 0 เพื่อให้รู้ว่าเป็น manual ในฝั่ง server (หรืออาจจะส่งค่า tax rate เดิมไปด้วยก็ได้ แต่นี่ง่ายกว่า)
                document.getElementById('withholdingTaxRate').value = 0;
            } else {
                // โหมดอัตโนมัติ
                const taxRate = parseFloat(document.getElementById('withholdingTaxRate').value) || 0;
                taxAmount = total * (taxRate / 100);
            }

            // ป้องกันภาษีเกินยอดรวม
            if (taxAmount > total) {
                taxAmount = total;
                if (isManualMode) {
                    document.getElementById('manualTaxInput').value = total.toFixed(2);
                }
            }

            document.getElementById('withholdingTaxAmount').value = taxAmount.toFixed(2);
            document.getElementById('withholdingTaxAmountDisplay').textContent =
                '-' + taxAmount.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' บาท';

            // คำนวณยอดสุทธิ
            let netAmount = total - taxAmount;
            if (netAmount < 0) netAmount = 0;

            document.getElementById('netAmount').value = netAmount.toFixed(2);
            document.getElementById('netTotalDisplay').textContent =
                netAmount.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' บาท';
        }

        // Event listeners
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('item-amount')) {
                calculateTotal();
            }
        });

        // คำนวณเมื่อเปลี่ยนอัตราภาษี (Auto Mode)
        document.getElementById('withholdingTaxRate').addEventListener('change', calculateTotal);

        // คำนวณเมื่อกรอกภาษีเอง (Manual Mode)
        document.getElementById('manualTaxInput').addEventListener('input', calculateTotal);

        // Switch Mode Logic
        document.getElementById('manualTaxSwitch').addEventListener('change', function(e) {
            const isManual = e.target.checked;
            const autoModeDiv = document.getElementById('taxAutoMode');
            const manualModeDiv = document.getElementById('taxManualMode');

            if (isManual) {
                autoModeDiv.classList.add('d-none');
                manualModeDiv.classList.remove('d-none');
                document.getElementById('manualTaxInput').focus();
            } else {
                autoModeDiv.classList.remove('d-none');
                manualModeDiv.classList.add('d-none');
                // Reset manual input
                document.getElementById('manualTaxInput').value = '';
            }
            calculateTotal();
        });

        // Preview voucher attachments
        document.getElementById('voucherAttachments').addEventListener('change', function(e) {
            const preview = document.getElementById('voucherAttachmentPreview');
            preview.innerHTML = '';

            Array.from(e.target.files).forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'attachment-item';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    item.appendChild(img);
                } else {
                    const icon = document.createElement('div');
                    icon.className = 'file-icon';
                    icon.innerHTML = '<i class="fa fa-file-pdf-o fa-2x text-danger"></i>';
                    item.appendChild(icon);
                }

                const name = document.createElement('small');
                name.className = 'd-block text-center text-truncate';
                name.style.maxWidth = '80px';
                name.textContent = file.name;
                item.appendChild(name);

                preview.appendChild(item);
            });
        });

        // Form validation
        document.getElementById('voucherForm').addEventListener('submit', function(e) {
            const amounts = document.querySelectorAll('.item-amount');
            let hasValidItem = false;
            amounts.forEach(input => {
                if (parseFloat(input.value) > 0) {
                    hasValidItem = true;
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                alert('กรุณาเพิ่มรายการอย่างน้อย 1 รายการที่มีจำนวนเงินมากกว่า 0');
            }
        });

        // Update voucher number preview when branch changes - ดึงจาก server
        document.getElementById('branchCode').addEventListener('change', function() {
            const branchCode = this.value;
            const previewInput = document.getElementById('voucherNoPreview');

            // แสดงสถานะกำลังโหลด
            previewInput.value = 'กำลังโหลด...';

            // เรียก API เพื่อดึงเลขที่ใบสำคัญจ่ายถัดไปที่ถูกต้อง
            fetch(`{{ route('payment-voucher.next-voucher-no') }}?branch_code=${branchCode}`)
                .then(response => response.json())
                .then(data => {
                    previewInput.value = data.voucher_no;
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback to simple format if API fails
                    const year = new Date().getFullYear();
                    const month = String(new Date().getMonth() + 1).padStart(2, '0');
                    previewInput.value = branchCode + year + month + '01';
                });
        });

        // Initialize Select2 for payee search
        $(document).ready(function() {
            $('#payToSelect').select2({
                placeholder: 'พิมพ์ชื่อผู้รับเงินหรือบริษัท...',
                allowClear: true,
                tags: true, // อนุญาตให้เพิ่มข้อมูลใหม่
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true // เพิ่ม flag เพื่อระบุว่าเป็นรายการใหม่
                    };
                },
                ajax: {
                    url: '{{ route('payment-voucher.search-payee') }}',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.name, // ใช้ชื่อเป็น value
                                    text: item.name + (item.account_no ? ' (' + item
                                        .account_no + ')' : ''),
                                    payee: item // เก็บข้อมูลทั้งหมดไว้
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                language: {
                    noResults: function() {
                        return 'ไม่พบข้อมูล - คุณสามารถพิมพ์ชื่อใหม่ได้';
                    },
                    searching: function() {
                        return 'กำลังค้นหา...';
                    }
                }
            });

            // เมื่อเลือก payee จะ auto-fill ที่อยู่และเลขบัญชี
            $('#payToSelect').on('select2:select', function(e) {
                var data = e.params.data;

                if (data.payee) {
                    // กรณีเลือกจากรายการที่มีอยู่
                    $('#payeeId').val(data.payee.id);
                    $('#payToAddress').val(data.payee.address || '');
                    $('#payToAccountNo').val(data.payee.account_no || '');

                    // เลือกธนาคารถ้ามี
                    if (data.payee.bank_name) {
                        $('#payToBankName').val(data.payee.bank_name);
                    }
                } else if (data.newTag) {
                    // กรณีเป็นรายการใหม่ ล้างข้อมูล
                    $('#payeeId').val('');
                    $('#payToAddress').val('');
                    $('#payToAccountNo').val('');
                    $('#payToBankName').val('');
                }
            });

            // เมื่อ clear ข้อมูล
            $('#payToSelect').on('select2:clear', function(e) {
                $('#payeeId').val('');
                $('#payToAddress').val('');
                $('#payToAccountNo').val('');
                $('#payToBankName').val('');
            });
        });
    </script>
@endsection
