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
            <form action="{{ route('payment-voucher.store') }}" method="POST" id="voucherForm" enctype="multipart/form-data">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa fa-file-text-o"></i> ข้อมูลใบสำคัญจ่าย
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">สาขา <span class="required-mark">*</span></label>
                            <select name="branch_code" id="branchCode" class="form-select" required>
                                @foreach($branchCodes as $code => $name)
                                    <option value="{{ $code }}" {{ old('branch_code', 'EL') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เลขที่ใบสำคัญจ่าย (ตัวอย่าง)</label>
                            <input type="text" class="form-control" id="voucherNoPreview" value="{{ $voucherNo }}" readonly disabled>
                            <small class="text-muted">รูปแบบ: รหัสสาขา + ปี + เดือน + ลำดับ (เช่น EL20260101)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">วันที่ <span class="required-mark">*</span></label>
                            <input type="date" name="voucher_date" class="form-control" value="{{ old('voucher_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa fa-user"></i> ข้อมูลผู้รับเงิน
                        </h5>

                        <div class="mb-3">
                            <label class="form-label">จ่ายให้ (ชื่อ/บริษัท) <span class="required-mark">*</span></label>
                            <select name="pay_to" id="payToSelect" class="form-control" required style="width: 100%;">
                                <option value="">-- ระบุหรือค้นหาชื่อผู้รับเงิน --</option>
                                @if(isset($payees))
                                    @foreach($payees as $payee)
                                        <option value="{{ $payee }}" {{ old('pay_to') == $payee ? 'selected' : '' }}>{{ $payee }}</option>
                                    @endforeach
                                @endif
                                @if(old('pay_to') && (!isset($payees) || !$payees->contains(old('pay_to'))))
                                    <option value="{{ old('pay_to') }}" selected>{{ old('pay_to') }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ที่อยู่</label>
                            <textarea name="pay_to_address" class="form-control" rows="2" placeholder="ที่อยู่ผู้รับเงิน">{{ old('pay_to_address') }}</textarea>
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
                        <input type="file" name="voucher_attachments[]" id="voucherAttachments" class="form-control" multiple accept="image/*,.pdf">
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
                                    <input type="text" class="form-control text-center item-no" value="1" readonly>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">รายละเอียด <span class="required-mark">*</span></label>
                                    <input type="text" name="items[0][description]" class="form-control" placeholder="รายละเอียดการจ่าย" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">จำนวนเงิน <span class="required-mark">*</span></label>
                                    <input type="number" name="items[0][amount]" class="form-control item-amount" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Slip/เอกสาร</label>
                                    <input type="file" name="items[0][attachments][]" class="form-control form-control-sm" multiple accept="image/*,.pdf">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-remove-item" onclick="removeItem(this)" disabled>
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
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea name="remark" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม">{{ old('remark') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="total-section">
                            <label class="form-label">ยอดรวมทั้งสิ้น</label>
                            <div class="total-amount" id="totalDisplay">0.00 บาท</div>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for Pay To field with tags support
    $('#payToSelect').select2({
        tags: true,
        placeholder: "-- ระบุหรือค้นหาชื่อผู้รับเงิน --",
        allowClear: true,
        theme: "bootstrap-5",
        width: '100%',
        language: {
            noResults: function() {
                return "ไม่พบข้อมูล - พิมพ์เพื่อเพิ่มรายชื่อใหม่";
            }
        }
    });

    // Auto-fill address when payee changes
    $('#payToSelect').on('select2:select', function (e) {
        var payee = e.params.data.id;
        if(payee) {
            // Disable address field while loading
            var addressField = $('textarea[name="pay_to_address"]');
            addressField.prop('disabled', true);
            var originalPlaceholder = addressField.attr('placeholder');
            addressField.attr('placeholder', 'กำลังค้นหาที่อยู่...');
            
            $.ajax({
                url: "{{ route('payment-voucher.get-address') }}",
                type: "GET",
                data: { payee: payee },
                success: function(response) {
                    if(response.address) {
                        addressField.val(response.address);
                    }
                },
                error: function() {
                    console.error('Failed to fetch address');
                },
                complete: function() {
                    addressField.prop('disabled', false);
                    addressField.attr('placeholder', originalPlaceholder);
                }
            });
        }
    });

    // Handle old value if it was a custom tag not in the list
    var oldPayTo = "{{ old('pay_to') }}";
    if(oldPayTo) {
        // Check if value already exists in options
        if ($('#payToSelect').find("option[value='" + oldPayTo + "']").length == 0) {
            var newOption = new Option(oldPayTo, oldPayTo, true, true);
            $('#payToSelect').append(newOption).trigger('change');
        }
    }
});

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
    document.getElementById('totalDisplay').textContent = 
        total.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท';
}

// Event listeners
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('item-amount')) {
        calculateTotal();
    }
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

// Update voucher number preview when branch changes
document.getElementById('branchCode').addEventListener('change', function() {
    const branchCode = this.value;
    const year = new Date().getFullYear();
    const month = String(new Date().getMonth() + 1).padStart(2, '0');
    // Generate preview with format: BranchCode + Year + Month + 01
    const newVoucherNo = branchCode + year + month + '01';
    document.getElementById('voucherNoPreview').value = newVoucherNo;
});
</script>
@endsection
