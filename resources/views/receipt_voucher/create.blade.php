@extends('layouts.main_template')

@section('style')
    <style>
        .item-row, .deduction-row { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #dee2e6; }
        .required-mark { color: red; }
        .total-section { background: #e7f3ff; padding: 20px; border-radius: 8px; border: 2px solid #0d6efd; }
        .total-amount { font-size: 1.5rem; font-weight: bold; color: #0d6efd; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon"><i class="pe-7s-note2 icon-gradient bg-tempting-azure"></i></div>
                    <div>สร้างใบสำคัญรับ (Receipt Voucher)<div class="page-title-subheading">กรอกข้อมูลเพื่อสร้างใบสำคัญรับใหม่</div></div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('receipt-voucher.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> กลับ</a>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <form action="{{ route('receipt-voucher.store') }}" method="POST" enctype="multipart/form-data" id="voucherForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">ข้อมูลหลัก</h5>
                            <div class="mb-3">
                                <label class="form-label">วันที่ <span class="required-mark">*</span></label>
                                <input type="date" name="voucher_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">เลขที่ใบสำคัญรับ (ตัวอย่าง)</label>
                                <input type="text" class="form-control" id="voucherNoPreview" value="{{ $voucherNo }}" readonly disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">สาขา / แผนก</label>
                                <select name="branch_code" id="branchCode" class="form-control">
                                    @foreach($branchCodes as $code => $name)
                                        <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">ข้อมูลผู้รับเงิน / รับจาก</h5>
                            <div class="mb-3">
                                <label class="form-label">รับจาก (ชื่อ/บริษัท) <span class="required-mark">*</span></label>
                                <input type="text" name="receive_from" class="form-control" required placeholder="ชื่อผู้ที่ได้รับเงินมา">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea name="receive_from_address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">เลขที่บัญชี (ถ้ามี)</label>
                                    <input type="text" name="receive_from_account_no" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ธนาคาร (ถ้ามี)</label>
                                    <input type="text" name="receive_from_bank_name" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3 mt-4">รายการรับ <span class="required-mark">*</span></h5>
                    <div id="itemsContainer">
                        <div class="item-row">
                            <div class="row align-items-end">
                                <div class="col-md-1">
                                    <label class="form-label">ลำดับ</label>
                                    <input type="text" class="form-control text-center item-no" value="1" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">รายละเอียด <span class="required-mark">*</span></label>
                                    <input type="text" name="items[0][description]" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">จำนวนเงิน <span class="required-mark">*</span></label>
                                    <input type="number" name="items[0][amount]" class="form-control item-amount" step="0.01" min="0" required placeholder="0.00">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-remove-item" onclick="removeItem(this)" disabled><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="addItem()"><i class="fa fa-plus"></i> เพิ่มรายการรับ</button>

                    <h5 class="border-bottom pb-2 mb-3 mt-4 text-danger">รายการหัก (ถ้ามี)</h5>
                    <div id="deductionsContainer"></div>
                    <button type="button" class="btn btn-outline-danger btn-sm mb-4" onclick="addDeduction()"><i class="fa fa-plus"></i> เพิ่มรายการหัก</button>

                    <div class="row mt-4">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea name="remark" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">แนบไฟล์ (เอกสารอ้างอิง/หลักฐานการรับเงิน)</label>
                                <input type="file" name="attachments[]" class="form-control" multiple>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="total-section">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ยอดรวมรับ:</span>
                                    <span id="subTotalDisplay">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span>ยอดรวมหัก:</span>
                                    <span id="deductionTotalDisplay">0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">จำนวนเงินสุทธิ:</span>
                                    <div class="total-amount" id="netTotalDisplay">0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-5 border-top pt-3">
                        <button type="submit" name="submit" value="0" class="btn btn-secondary btn-lg me-2">
                            <i class="fa fa-save"></i> บันทึกฉบับร่าง
                        </button>
                        <button type="submit" name="submit" value="1" class="btn btn-primary btn-lg">
                            <i class="fa fa-paper-plane"></i> บันทึกและส่งตรวจสอบ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let itemIndex = 1;
        let deductionIndex = 0;

        function addItem() {
            const html = `
                <div class="item-row">
                    <div class="row align-items-end">
                        <div class="col-md-1">
                            <input type="text" class="form-control text-center item-no" value="${itemIndex + 1}" readonly>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="items[${itemIndex}][description]" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger" onclick="removeItem(this)"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
            itemIndex++;
            updateNumbers('item-no');
            updateRemoveButtons('btn-remove-item');
        }

        function addDeduction() {
            const html = `
                <div class="deduction-row">
                    <div class="row align-items-end">
                        <div class="col-md-1">
                            <input type="text" class="form-control text-center deduction-no" value="${deductionIndex + 1}" readonly>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="deductions[${deductionIndex}][description]" class="form-control" required placeholder="เช่น ค่าธรรมเนียม">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="deductions[${deductionIndex}][amount]" class="form-control deduction-amount" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger" onclick="removeDeduction(this)"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            document.getElementById('deductionsContainer').insertAdjacentHTML('beforeend', html);
            deductionIndex++;
            updateNumbers('deduction-no');
        }

        function removeItem(btn) {
            btn.closest('.item-row').remove();
            updateNumbers('item-no');
            updateRemoveButtons('btn-remove-item');
            calculateTotal();
        }

        function removeDeduction(btn) {
            btn.closest('.deduction-row').remove();
            updateNumbers('deduction-no');
            calculateTotal();
        }

        function updateNumbers(className) {
            const inputs = document.querySelectorAll('.' + className);
            inputs.forEach((input, i) => input.value = i + 1);
        }

        function updateRemoveButtons(className) {
            const buttons = document.querySelectorAll('.btn-remove-item');
            if (buttons.length === 1) buttons[0].disabled = true;
            else buttons.forEach(b => b.disabled = false);
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-amount').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('subTotalDisplay').innerText = total.toLocaleString('th-TH', {minimumFractionDigits: 2});

            let deductionTotal = 0;
            document.querySelectorAll('.deduction-amount').forEach(input => {
                deductionTotal += parseFloat(input.value) || 0;
            });
            document.getElementById('deductionTotalDisplay').innerText = deductionTotal.toLocaleString('th-TH', {minimumFractionDigits: 2});

            const netTotal = total - deductionTotal;
            document.getElementById('netTotalDisplay').innerText = netTotal.toLocaleString('th-TH', {minimumFractionDigits: 2});
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('item-amount') || e.target.classList.contains('deduction-amount')) {
                calculateTotal();
            }
        });

        // Voucher No Preview
        document.getElementById('branchCode').addEventListener('change', function() {
            const branchCode = this.value;
            fetch(`{{ route('receipt-voucher.next-voucher-no') }}?branch_code=${branchCode}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('voucherNoPreview').value = data.voucher_no;
                });
        });
    </script>
@endsection
