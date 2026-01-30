@extends('layouts.main_template')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-users icon-gradient bg-malibu-beach"></i>
                </div>
                <div>
                    แก้ไขเงินเดือนแบบกลุ่ม (Bulk Edit)
                    <div class="page-title-subheading">งวด: {{ $period->code }}</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> กลับ
                </a>
                <button type="submit" form="main-bulk-form" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> บันทึกทั้งหมด
                </button>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('payroll.bulk-update', $period->id) }}" method="POST" id="main-bulk-form">
        @csrf

        <div class="accordion" id="payrollAccordion">
            @foreach ($period->payrolls as $payroll)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $payroll->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $payroll->id }}" aria-expanded="false"
                            aria-controls="collapse{{ $payroll->id }}">
                            <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                <div>
                                    <strong>{{ $payroll->employee_code }} {{ $payroll->employee_name }}</strong>
                                    <span class="text-muted ms-2">({{ $payroll->position }})</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-label-primary me-2">ฐานเงินเดือน:
                                        {{ number_format($payroll->base_salary, 2) }}</span>
                                    <span class="badge bg-success">Net: {{ number_format($payroll->net_pay, 2) }}</span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $payroll->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading{{ $payroll->id }}" data-bs-parent="#payrollAccordion">
                        <div class="accordion-body bg-light">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-info text-white"
                                    onclick="saveIndividual({{ $payroll->id }})">
                                    <i class="fas fa-save me-1"></i> บันทึกรายการนี้
                                </button>
                            </div>
                            <div class="row">
                                {{-- Settings & Base Salary --}}
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="fw-bold">ข้อมูลพื้นฐาน & การหัก</h6>

                                            {{-- Hidden ID for explicit key matching --}}
                                            <input type="hidden" name="payrolls[{{ $payroll->id }}][id]"
                                                value="{{ $payroll->id }}">

                                            <div class="mb-3">
                                                <label class="form-label">เงินเดือน (Base)</label>
                                                <input type="number" step="0.01" class="form-control"
                                                    name="payrolls[{{ $payroll->id }}][base_salary]"
                                                    value="{{ $payroll->base_salary }}">
                                            </div>

                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="payrolls[{{ $payroll->id }}][calc_social_security]"
                                                    value="1" {{ $payroll->calc_social_security ? 'checked' : '' }}>
                                                <label class="form-check-label">หักประกันสังคม (Auto)</label>
                                            </div>
                                            <div class="input-group input-group-sm mb-3">
                                                <span class="input-group-text">อัตรา</span>
                                                <input type="number" class="form-control"
                                                    name="payrolls[{{ $payroll->id }}][social_security_rate]"
                                                    value="{{ $payroll->social_security_rate }}" step="0.01"
                                                    min="0" max="100">
                                                <span class="input-group-text">%</span>
                                            </div>

                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="payrolls[{{ $payroll->id }}][calc_tax]" value="1"
                                                    {{ $payroll->calc_tax ? 'checked' : '' }}>
                                                <label class="form-check-label">หักภาษี (Auto)</label>
                                            </div>

                                            <hr>

                                            {{-- Late Deduction Input --}}
                                            @php
                                                $late_item = $payroll->deductionItems
                                                    ->where('deduction_type', 'late')
                                                    ->first();
                                                $late_minutes = 0;
                                                if ($late_item) {
                                                    $late_minutes = $late_item->rate > 0 ? (int) $late_item->rate : 0;
                                                }
                                            @endphp
                                            <label class="form-label text-danger">มาสาย (นาที)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control"
                                                    name="payrolls[{{ $payroll->id }}][late_minutes]"
                                                    value="{{ $late_minutes }}" step="1" min="0">
                                                <span class="input-group-text">นาที</span>
                                            </div>

                                            {{-- Absence Deduction Input --}}
                                            @php
                                                $absence_item = $payroll->deductionItems
                                                    ->where('deduction_type', 'absence')
                                                    ->first();
                                                $absence_days = 0;
                                                if ($absence_item) {
                                                    $absence_days =
                                                        $absence_item->rate > 0 ? (float) $absence_item->rate : 0;
                                                }
                                            @endphp
                                            <label class="form-label text-danger mt-2">ขาดงาน (วัน)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control"
                                                    name="payrolls[{{ $payroll->id }}][absence_days]"
                                                    value="{{ $absence_days }}" step="0.5" min="0">
                                                <span class="input-group-text">วัน</span>
                                            </div>

                                            <small class="text-muted">ระบบจะคำนวณยอดเงินหักให้อัตโนมัติ</small>

                                            <hr class="my-3">

                                            <div class="mb-2">
                                                <label class="form-label">หมายเหตุ</label>
                                                <textarea name="payrolls[{{ $payroll->id }}][remark]" class="form-control" rows="2">{{ $payroll->remark }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Income & Deductions --}}
                                <div class="col-md-8">
                                    {{-- Income --}}
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <h6 class="mb-0 text-success"><i class="fas fa-plus-circle me-1"></i>
                                                รายการรายได้</h6>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="addIncomeItem({{ $payroll->id }})">
                                                <i class="fas fa-plus"></i> เพิ่ม
                                            </button>
                                        </div>
                                        <div class="card-body p-2" id="income-list-{{ $payroll->id }}">
                                            @foreach ($payroll->incomeItems as $item)
                                                @php $rowId = 'income-' . $payroll->id . '-' . $loop->index . '-' . uniqid(); @endphp
                                                <div class="row g-1 mb-2 align-items-center item-row"
                                                    id="{{ $rowId }}">
                                                    <div class="col-3">
                                                        <select
                                                            name="payrolls[{{ $payroll->id }}][income_items][{{ $loop->index }}][type]"
                                                            class="form-select form-select-sm">
                                                            @foreach (\App\Models\Payroll::$incomeTypes as $key => $label)
                                                                <option value="{{ $key }}"
                                                                    {{ $item->income_type == $key ? 'selected' : '' }}>
                                                                    {{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="payrolls[{{ $payroll->id }}][income_items][{{ $loop->index }}][description]"
                                                            value="{{ $item->description }}" placeholder="คำอธิบาย">
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" step="0.01"
                                                            class="form-control form-control-sm"
                                                            name="payrolls[{{ $payroll->id }}][income_items][{{ $loop->index }}][amount]"
                                                            value="{{ $item->amount }}" placeholder="จำนวนเงิน">
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="payrolls[{{ $payroll->id }}][income_items][{{ $loop->index }}][is_wht_3_percent]"
                                                                value="1"
                                                                {{ $item->is_wht_3_percent ? 'checked' : '' }}>
                                                            <label class="form-check-label" style="font-size: 0.8rem;">หัก
                                                                3%</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-danger border-0"
                                                            onclick="removeItem('{{ $rowId }}')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <input type="hidden"
                                                        name="payrolls[{{ $payroll->id }}][income_items][{{ $loop->index }}][quantity]"
                                                        value="{{ $item->quantity }}">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Deductions --}}
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <h6 class="mb-0 text-danger"><i class="fas fa-minus-circle me-1"></i>
                                                รายการหัก</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="addDeductionItem({{ $payroll->id }})">
                                                <i class="fas fa-plus"></i> เพิ่มรายการหักบริษัท
                                            </button>
                                        </div>
                                        <div class="card-body p-2" id="deduction-list-{{ $payroll->id }}">

                                            {{-- Group 1: Government --}}
                                            <h6 class="fw-bold text-muted border-bottom pb-1 mb-2"
                                                style="font-size: 0.85rem;">ส่วนของรัฐบาล & กองทุน (Government)</h6>
                                            @php
                                                $govDeductions = $payroll->deductionItems->filter(
                                                    fn($i) => in_array($i->deduction_type, [
                                                        'social_security',
                                                        'tax',
                                                        'wht_3_percent',
                                                    ]),
                                                );
                                            @endphp
                                            @foreach ($govDeductions as $item)
                                                <div
                                                    class="row g-1 mb-2 align-items-center bg-light p-1 rounded border-start border-3 border-info">
                                                    <div class="col-8">
                                                        <span
                                                            class="fw-semibold text-dark">{{ $item->description }}</span>
                                                    </div>
                                                    <div class="col-4 text-end">
                                                        <span
                                                            class="text-danger fw-bold deduction-amount">-{{ number_format($item->amount, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Group 2: Company --}}
                                            <h6 class="fw-bold text-muted border-bottom pb-1 mb-2 mt-3"
                                                style="font-size: 0.85rem;">ส่วนของบริษัท (Company) & มาสาย</h6>
                                            @php
                                                $compDeductions = $payroll->deductionItems->reject(
                                                    fn($i) => in_array($i->deduction_type, [
                                                        'social_security',
                                                        'tax',
                                                        'wht_3_percent',
                                                        'late',
                                                        'absence',
                                                    ]),
                                                );
                                                $lateItem = $payroll->deductionItems
                                                    ->where('deduction_type', 'late')
                                                    ->first();
                                            @endphp

                                            {{-- Late Item Handling (Displayed but read-only here, edited via inputs) --}}
                                            @if ($lateItem && $lateItem->amount > 0)
                                                <div
                                                    class="row g-1 mb-2 align-items-center bg-light p-1 rounded border-start border-3 border-warning">
                                                    <div class="col-8">
                                                        <span
                                                            class="fw-semibold text-dark">{{ $lateItem->description }}</span>
                                                    </div>
                                                    <div class="col-4 text-end">
                                                        <span
                                                            class="text-danger fw-bold">-{{ number_format($lateItem->amount, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            @foreach ($compDeductions as $item)
                                                @php $rowId = 'deduction-' . $payroll->id . '-' . $loop->index . '-' . uniqid(); @endphp
                                                <div class="row g-1 mb-2 align-items-center item-row"
                                                    id="{{ $rowId }}">
                                                    <div class="col-3">
                                                        <select
                                                            name="payrolls[{{ $payroll->id }}][deduction_items][{{ $loop->index }}][type]"
                                                            class="form-select form-select-sm">
                                                            @foreach (\App\Models\Payroll::$deductionTypes as $key => $label)
                                                                @if (!in_array($key, ['social_security', 'tax', 'late', 'wht_3_percent', 'absence']))
                                                                    <option value="{{ $key }}"
                                                                        {{ $item->deduction_type == $key ? 'selected' : '' }}>
                                                                        {{ $label }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="payrolls[{{ $payroll->id }}][deduction_items][{{ $loop->index }}][description]"
                                                            value="{{ $item->description }}" placeholder="คำอธิบาย">
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" step="0.01"
                                                            class="form-control form-control-sm"
                                                            name="payrolls[{{ $payroll->id }}][deduction_items][{{ $loop->index }}][amount]"
                                                            value="{{ $item->amount }}" placeholder="จำนวนเงิน">
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-danger border-0"
                                                            onclick="removeItem('{{ $rowId }}')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Submit Block --}}
        <div class="card mt-4">
            <div class="card-body text-end">
                <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-secondary me-2">ยกเลิก</a>
                <button type="submit" class="btn btn-primary btn-lg">บันทึกการแก้ไขทั้งหมด</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        const incomeTypes = @json(\App\Models\Payroll::$incomeTypes);
        const deductionTypes = @json(\App\Models\Payroll::$deductionTypes);
        const updateUrl = "{{ route('payroll.bulk-update', $period->id) }}";

        // Helper to generate options
        function getOptions(types, exclude = []) {
            let html = '';
            for (const [key, label] of Object.entries(types)) {
                if (!exclude.includes(key)) {
                    html += `<option value="${key}">${label}</option>`;
                }
            }
            return html;
        }

        function addIncomeItem(payrollId) {
            const container = document.getElementById(`income-list-${payrollId}`);
            const index = Date.now() + Math.floor(Math.random() * 1000);
            const rowId = `income-${payrollId}-${index}`;

            const html = `
            <div class="row g-1 mb-2 align-items-center item-row" id="${rowId}">
                <div class="col-3">
                    <select name="payrolls[${payrollId}][income_items][${index}][type]" class="form-select form-select-sm">
                        ${getOptions(incomeTypes)}
                    </select>
                </div>
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm"
                        name="payrolls[${payrollId}][income_items][${index}][description]"
                        placeholder="คำอธิบาย">
                </div>
                <div class="col-2">
                    <input type="number" step="0.01" class="form-control form-control-sm"
                        name="payrolls[${payrollId}][income_items][${index}][amount]"
                        placeholder="จำนวนเงิน">
                </div>
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                            name="payrolls[${payrollId}][income_items][${index}][is_wht_3_percent]"
                            value="1">
                        <label class="form-check-label" style="font-size: 0.8rem;">หัก 3%</label>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-xs btn-outline-danger border-0" onclick="removeItem('${rowId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="payrolls[${payrollId}][income_items][${index}][quantity]" value="1">
            </div>
        `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function addDeductionItem(payrollId) {
            const container = document.getElementById(`deduction-list-${payrollId}`);
            const index = Date.now() + Math.floor(Math.random() * 1000);
            const rowId = `deduction-${payrollId}-${index}`;

            const html = `
            <div class="row g-1 mb-2 align-items-center item-row" id="${rowId}">
                <div class="col-3">
                    <select name="payrolls[${payrollId}][deduction_items][${index}][type]" class="form-select form-select-sm">
                        ${getOptions(deductionTypes, ['social_security', 'tax', 'late', 'wht_3_percent', 'absence'])}
                    </select>
                </div>
                <div class="col-5">
                    <input type="text" class="form-control form-control-sm"
                        name="payrolls[${payrollId}][deduction_items][${index}][description]"
                        placeholder="คำอธิบาย">
                </div>
                <div class="col-2">
                    <input type="number" step="0.01" class="form-control form-control-sm"
                        name="payrolls[${payrollId}][deduction_items][${index}][amount]"
                        placeholder="จำนวนเงิน">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-xs btn-outline-danger border-0" onclick="removeItem('${rowId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeItem(rowId) {
            document.getElementById(rowId).remove();
        }

        // Individual Save Function
        function saveIndividual(payrollId) {
            // Collect Inputs strictly for this ID
            // The simple way: select input[name^="payrolls[123]"]
            const container = document.getElementById(`collapse${payrollId}`);
            const inputs = container.querySelectorAll('input, select, textarea');

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            let hasData = false;
            inputs.forEach(input => {
                if (input.name && input.name.startsWith(`payrolls[${payrollId}]`)) {
                    if (input.type === 'checkbox') {
                        if (input.checked) {
                            formData.append(input.name, input.value);
                        }
                    } else if (input.type === 'radio') {
                        if (input.checked) {
                            formData.append(input.name, input.value);
                        }
                    } else {
                        formData.append(input.name, input.value);
                    }
                    hasData = true;
                }
            });

            if (!hasData) return;

            // Show Loading (Optional, maybe spinner on button)
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...';

            fetch(updateUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        toastr.success('บันทึกข้อมูลเรียบร้อยแล้ว');
                        // Optional: Update Badge UI if response contained new totals
                    } else {
                        toastr.error(data.message || 'เกิดข้อผิดพลาด');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }
    </script>
@endsection
