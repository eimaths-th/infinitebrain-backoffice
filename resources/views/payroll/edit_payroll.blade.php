@extends('layouts.main_template')

@section('style')
    <style>
        .item-row {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .remove-item-btn {
            color: #dc3545;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-user-edit me-2"></i>แก้ไขเงินเดือน: {{ $payroll->employee_name }}
                </h4>
                <p class="text-muted mb-0">
                    งวด {{ $period->period_name }} | {{ $payroll->GetEmployeeTypeLabel() }}
                </p>
            </div>
            <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> กลับ
            </a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('payroll.update-payroll', [$period->id, $payroll->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Left Column: Employee Info & Options -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">ข้อมูลพนักงาน</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">รหัส:</td>
                                    <td>{{ $payroll->employee_code }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ตำแหน่ง:</td>
                                    <td>{{ $payroll->position }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ประเภท:</td>
                                    <td>{{ $payroll->GetEmployeeTypeLabel() }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ธนาคาร:</td>
                                    <td>{{ $payroll->bank_name }} {{ $payroll->bank_account }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">ตัวเลือกการคำนวณ</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="calc_social_security"
                                    id="calc_social_security" value="1"
                                    {{ $payroll->calc_social_security ? 'checked' : '' }}>
                                <label class="form-check-label" for="calc_social_security">
                                    คำนวณประกันสังคม
                                </label>
                                <div class="mt-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">อัตรา</span>
                                        <input type="number" name="social_security_rate" class="form-control"
                                            value="{{ $payroll->social_security_rate }}" step="0.01" min="0"
                                            max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="calc_tax" id="calc_tax"
                                    value="1" {{ $payroll->calc_tax ? 'checked' : '' }}>
                                <label class="form-check-label" for="calc_tax">
                                    คำนวณภาษี
                                </label>
                                <div class="mt-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">อัตรา</span>
                                        <input type="number" name="tax_rate" class="form-control"
                                            value="{{ $payroll->tax_rate }}" step="0.01" min="0" max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            @php
                                // ดึงค่านาทีมาสายจากรายการหัก late ที่มีอยู่
                                $late_item = $payroll->deductionItems->where('deduction_type', 'late')->first();
                                $existing_late_minutes = 0;
                                if ($late_item && preg_match('/(\d+)\s*นาที/', $late_item->description, $matches)) {
                                    $existing_late_minutes = (int) $matches[1];
                                }
                            @endphp

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-clock text-warning me-1"></i>หักมาสาย
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="late_minutes" class="form-control"
                                        value="{{ old('late_minutes', $existing_late_minutes) }}" min="0"
                                        step="1" placeholder="ระบุนาทีที่มาสาย">
                                    <span class="input-group-text">นาที</span>
                                </div>
                                <small class="text-muted">
                                    สูตร: (เงินเดือน ÷ 30 ÷ 8 ÷ 60) × นาทีมาสาย
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">หมายเหตุ</h6>
                        </div>
                        <div class="card-body">
                            <textarea name="remark" class="form-control" rows="3">{{ $payroll->remark }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Income & Deductions -->
                <div class="col-md-8">
                    <!-- Income Items -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>รายการรายได้</h6>
                            <button type="button" class="btn btn-success btn-sm" onclick="addIncomeItem()">
                                <i class="fas fa-plus me-1"></i> เพิ่มรายการ
                            </button>
                        </div>
                        <div class="card-body" id="income-items-container">
                            @forelse($payroll->incomeItems as $index => $item)
                                <div class="item-row" id="income-row-{{ $index }}">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <select name="income_items[{{ $index }}][type]"
                                                class="form-select form-select-sm">
                                                @foreach (\App\Models\Payroll::$incomeTypes as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ $item->income_type == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="income_items[{{ $index }}][description]"
                                                class="form-control form-control-sm" placeholder="คำอธิบาย"
                                                value="{{ $item->description }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="income_items[{{ $index }}][quantity]"
                                                class="form-control form-control-sm" placeholder="จำนวน"
                                                value="{{ $item->quantity }}" step="0.01">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="income_items[{{ $index }}][amount]"
                                                class="form-control form-control-sm" placeholder="จำนวนเงิน"
                                                value="{{ $item->amount }}" step="0.01">
                                        </div>
                                        <div class="col-md-1">
                                            <span class="remove-item-btn"
                                                onclick="removeItem('income-row-{{ $index }}')">
                                                <i class="fas fa-times-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">ยังไม่มีรายการรายได้</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Deduction Items (Manual only, auto items will be calculated) -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-minus-circle me-2 text-danger"></i>รายการหักเพิ่มเติม</h6>
                            <button type="button" class="btn btn-danger btn-sm" onclick="addDeductionItem()">
                                <i class="fas fa-plus me-1"></i> เพิ่มรายการ
                            </button>
                        </div>
                        <div class="card-body" id="deduction-items-container">
                            @php
                                $manualDeductions = $payroll->deductionItems->whereNotIn('deduction_type', [
                                    'social_security',
                                    'tax',
                                ]);
                            @endphp
                            @forelse($manualDeductions as $index => $item)
                                <div class="item-row" id="deduction-row-{{ $index }}">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <select name="deduction_items[{{ $index }}][type]"
                                                class="form-select form-select-sm">
                                                @foreach (\App\Models\Payroll::$deductionTypes as $key => $label)
                                                    @if (!in_array($key, ['social_security', 'tax']))
                                                        <option value="{{ $key }}"
                                                            {{ $item->deduction_type == $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text"
                                                name="deduction_items[{{ $index }}][description]"
                                                class="form-control form-control-sm" placeholder="คำอธิบาย"
                                                value="{{ $item->description }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="deduction_items[{{ $index }}][amount]"
                                                class="form-control form-control-sm" placeholder="จำนวนเงิน"
                                                value="{{ $item->amount }}" step="0.01">
                                        </div>
                                        <div class="col-md-1">
                                            <span class="remove-item-btn"
                                                onclick="removeItem('deduction-row-{{ $index }}')">
                                                <i class="fas fa-times-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center mb-0">ไม่มีรายการหักเพิ่มเติม (ปกส.
                                    และภาษีจะคำนวณอัตโนมัติ)</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> บันทึก
                        </button>
                        <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-secondary">
                            ยกเลิก
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        let incomeIndex = {{ $payroll->incomeItems->count() }};
        let deductionIndex =
            {{ $payroll->deductionItems->whereNotIn('deduction_type', ['social_security', 'tax'])->count() }};

        // Income types from PHP
        const incomeTypes = @json(\App\Models\Payroll::$incomeTypes);

        function addIncomeItem() {
            const container = document.getElementById('income-items-container');

            // Generate options HTML
            let optionsHtml = '';
            for (const [key, label] of Object.entries(incomeTypes)) {
                optionsHtml += `<option value="${key}">${label}</option>`;
            }

            const html = `
        <div class="item-row" id="income-row-${incomeIndex}">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="income_items[${incomeIndex}][type]" class="form-select form-select-sm">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="income_items[${incomeIndex}][description]"
                           class="form-control form-control-sm" placeholder="คำอธิบาย">
                </div>
                <div class="col-md-2">
                    <input type="number" name="income_items[${incomeIndex}][quantity]"
                           class="form-control form-control-sm" placeholder="จำนวน" value="1" step="0.01">
                </div>
                <div class="col-md-2">
                    <input type="number" name="income_items[${incomeIndex}][amount]"
                           class="form-control form-control-sm" placeholder="จำนวนเงิน" step="0.01">
                </div>
                <div class="col-md-1">
                    <span class="remove-item-btn" onclick="removeItem('income-row-${incomeIndex}')">
                        <i class="fas fa-times-circle"></i>
                    </span>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            incomeIndex++;
        }

        function addDeductionItem() {
            const container = document.getElementById('deduction-items-container');
            const html = `
        <div class="item-row" id="deduction-row-${deductionIndex}">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="deduction_items[${deductionIndex}][type]" class="form-select form-select-sm">
                        <option value="loan">เงินกู้</option>
                        <option value="advance">เงินทดรอง</option>
                        <option value="other">หักอื่นๆ</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" name="deduction_items[${deductionIndex}][description]"
                           class="form-control form-control-sm" placeholder="คำอธิบาย">
                </div>
                <div class="col-md-3">
                    <input type="number" name="deduction_items[${deductionIndex}][amount]"
                           class="form-control form-control-sm" placeholder="จำนวนเงิน" step="0.01">
                </div>
                <div class="col-md-1">
                    <span class="remove-item-btn" onclick="removeItem('deduction-row-${deductionIndex}')">
                        <i class="fas fa-times-circle"></i>
                    </span>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            deductionIndex++;
        }

        function removeItem(id) {
            document.getElementById(id)?.remove();
        }
    </script>
@endsection
