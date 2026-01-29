@extends('layouts.main_template')

@section('style')
    <style>
        .bulk-edit-table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .input-number {
            text-align: right;
            min-width: 80px;
        }

        .dynamic-col-header {
            background-color: #e8f5e9 !important;
            /* Light green for income */
            position: relative;
        }

        .dynamic-col-header.deduction {
            background-color: #ffebee !important;
            /* Light red for deduction */
        }

        .remove-col-btn {
            position: absolute;
            top: -10px;
            right: -5px;
            color: red;
            cursor: pointer;
            z-index: 20;
            background: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-edit me-2"></i>แก้ไขเงินเดือนหลายคน (Bulk Edit)
                </h4>
                <p class="text-muted mb-0">
                    งวด: {{ $period->period_name }}
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-success me-1" onclick="showAddColumnModal('income')">
                    <i class="fas fa-plus-circle me-1"></i> เพิ่มคอลัมน์รายได้
                </button>
                <button type="button" class="btn btn-danger me-2" onclick="showAddColumnModal('deduction')">
                    <i class="fas fa-minus-circle me-1"></i> เพิ่มคอลัมน์รายจ่าย
                </button>
                <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> ยกเลิก/กลับ
                </a>
                <button type="submit" form="bulk-edit-form" class="btn btn-primary ms-2">
                    <i class="fas fa-save me-1"></i> บันทึกทั้งหมด
                </button>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <form action="{{ route('payroll.bulk-update', $period->id) }}" method="POST" id="bulk-edit-form">
                    @csrf
                    <!-- Container for Dynamic Column Definitions -->
                    <div id="dynamic-cols-definitions"></div>

                    <div class="table-responsive" style="max-height: 70vh;">
                        <table class="table table-bordered table-hover mb-0 bulk-edit-table" id="bulk-table">
                            <thead class="table-light">
                                <tr id="header-row">
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 100px;">รหัส</th>
                                    <th style="width: 200px;">ชื่อ-นามสกุล</th>
                                    <th style="width: 150px;" class="text-end">เงินเดือน (Base)</th>
                                    <th style="width: 150px;" class="text-end">ค่าล่วงเวลา (OT)</th>

                                    <!-- Dynamic Columns will be inserted here -->
                                    <th id="marker-dynamic-start" style="display:none;"></th>

                                    <th style="width: 80px;" class="text-center">ปกส.</th>
                                    <th style="width: 80px;" class="text-center">ภาษี</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($period->payrolls as $index => $payroll)
                                    <tr data-payroll-id="{{ $payroll->id }}">
                                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle">{{ $payroll->employee_code }}</td>
                                        <td class="align-middle">
                                            <strong>{{ $payroll->employee_name }}</strong><br>
                                            <small class="text-muted">{{ $payroll->position }}</small>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control input-number"
                                                name="payrolls[{{ $payroll->id }}][base_salary]"
                                                value="{{ $payroll->base_salary }}">
                                        </td>
                                        <td>
                                            @php
                                                $ot_item = $payroll->incomeItems
                                                    ->where('income_type', \App\Models\Payroll::INCOME_OT)
                                                    ->first();
                                                $ot_amount = $ot_item ? $ot_item->amount : 0;
                                            @endphp
                                            <input type="number" step="0.01" class="form-control input-number"
                                                name="payrolls[{{ $payroll->id }}][ot]" value="{{ $ot_amount }}">
                                        </td>

                                        <!-- Dynamic Inputs will be inserted here by JS -->

                                        <td class="text-center align-middle cell-sso">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox"
                                                    name="payrolls[{{ $payroll->id }}][calc_social_security]"
                                                    value="1" {{ $payroll->calc_social_security ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle cell-tax">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox"
                                                    name="payrolls[{{ $payroll->id }}][calc_tax]" value="1"
                                                    {{ $payroll->calc_tax ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3 text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            หมายเหตุ: คอลัมน์ที่เพิ่มขึ้นมาใหม่ จะบันทึกทับรายการเดิมที่มี "ประเภท" และ "คำอธิบาย" ตรงกัน
        </div>
    </div>

    <!-- Modal for Adding Column -->
    <div class="modal fade" id="addColumnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มคอลัมน์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="col-group-type"> <!-- income or deduction -->
                    <div class="mb-3">
                        <label class="form-label">ประเภทรายการ</label>
                        <select class="form-select" id="col-item-type">
                            <!-- JS will populate -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำอธิบายเพิ่มเติม (ถ้ามี)</label>
                        <input type="text" class="form-control" id="col-description" placeholder="เช่น โบนัสปีใหม่">
                        <small class="text-muted">ใช้เพื่อแยกประเภทรายการเดียวกันออกจากกัน</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="confirmAddColumn()">เพิ่มคอลัมน์</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $existingIncomes = $period->payrolls
        ->pluck('incomeItems')
        ->flatten()
        ->whereNotIn('income_type', [\App\Models\Payroll::INCOME_SALARY, \App\Models\Payroll::INCOME_OT])
        ->unique(fn($item) => $item->income_type . '-' . $item->description)
        ->map(
            fn($item) => [
                'type' => $item->income_type,
                'description' => $item->description,
            ],
        )
        ->values();

    $existingDeductions = $period->payrolls
        ->pluck('deductionItems')
        ->flatten()
        ->whereNotIn('deduction_type', [
            \App\Models\Payroll::DEDUCTION_SOCIAL_SECURITY,
            \App\Models\Payroll::DEDUCTION_TAX,
        ])
        ->unique(fn($item) => $item->deduction_type . '-' . $item->description)
        ->map(
            fn($item) => [
                'type' => $item->deduction_type,
                'description' => $item->description,
            ],
        )
        ->values();

    $payrollsData = $period->payrolls->map(function ($p) {
        return [
            'id' => $p->id,
            'incomes' => $p->incomeItems,
            'deductions' => $p->deductionItems,
        ];
    });
@endphp

@section('script')
    <script>
        const incomeTypes = @json(\App\Models\Payroll::$incomeTypes);
        const deductionTypes = @json(\App\Models\Payroll::$deductionTypes);
        let colCounter = 0;

        // Collect existing items to auto-create columns
        const existingIncomes = @json($existingIncomes);
        const existingDeductions = @json($existingDeductions);

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize existing columns
            existingIncomes.forEach(item => {
                addColumn('income', item.type, item.description);
            });
            existingDeductions.forEach(item => {
                addColumn('deduction', item.type, item.description);
            });

            // Fill values for these columns
            fillExistingValues();
        });

        function showAddColumnModal(groupType) {
            document.getElementById('col-group-type').value = groupType;
            const select = document.getElementById('col-item-type');
            select.innerHTML = '';

            const types = groupType === 'income' ? incomeTypes : deductionTypes;

            for (const [key, label] of Object.entries(types)) {
                if (groupType === 'deduction' && (key === 'social_security' || key === 'tax')) continue;
                if (groupType === 'income' && key === 'salary') continue;

                const option = document.createElement('option');
                option.value = key;
                option.text = label;
                select.appendChild(option);
            }

            updateDescriptionPlaceholder();
            select.onchange = updateDescriptionPlaceholder;

            new bootstrap.Modal(document.getElementById('addColumnModal')).show();
        }

        function updateDescriptionPlaceholder() {
            const select = document.getElementById('col-item-type');
            const text = select.options[select.selectedIndex].text;
            document.getElementById('col-description').value = text;
        }

        function confirmAddColumn() {
            const groupType = document.getElementById('col-group-type').value;
            const typeKey = document.getElementById('col-item-type').value;
            const description = document.getElementById('col-description').value || document.getElementById('col-item-type')
                .options[document.getElementById('col-item-type').selectedIndex].text;

            addColumn(groupType, typeKey, description);
            bootstrap.Modal.getInstance(document.getElementById('addColumnModal')).hide();
        }

        function addColumn(groupType, typeKey, description) {
            const uuid = Date.now() + '_' + colCounter++;

            // 1. Add Definition Hidden Inputs
            const defContainer = document.getElementById('dynamic-cols-definitions');
            defContainer.innerHTML += `
            <input type="hidden" name="cols[${groupType}][${uuid}][type]" value="${typeKey}" data-uuid="${uuid}" data-type="${typeKey}" data-desc="${description}" class="col-def-${groupType}">
            <input type="hidden" name="cols[${groupType}][${uuid}][description]" value="${description}">
        `;

            // 2. Add Header Column
            const headerRow = document.getElementById('header-row');
            const marker = document.getElementById('marker-dynamic-start');

            const newTh = document.createElement('th');
            newTh.className = `text-end dynamic-col-header ${groupType}`;
            newTh.style.minWidth = "120px";
            newTh.innerHTML = `
            ${description}
            <span class="remove-col-btn" onclick="removeColumn(this, '${uuid}')">&times;</span>
        `;

            marker.parentElement.insertBefore(newTh, marker.nextSibling);

            // 3. Add Input Cells to Body Rows
            const rows = document.querySelectorAll('#bulk-table tbody tr');
            rows.forEach(row => {
                const newTd = document.createElement('td');
                newTd.className = `dynamic-col-${uuid}`;
                // We set a data attribute to help with value filling
                newTd.innerHTML = `
                <input type="number" step="0.01" class="form-control input-number dynamic-input"
                       name="payrolls[${row.dataset.payrollId}][extra_${groupType}][${uuid}]"
                       data-payroll-id="${row.dataset.payrollId}"
                       data-group="${groupType}"
                       data-type="${typeKey}"
                       data-desc="${description}"
                       placeholder="0.00">
            `;

                const ssoCell = row.querySelector('.cell-sso');
                row.insertBefore(newTd, ssoCell);
            });
        }

        function fillExistingValues() {
            // This function iterates over all payrolls (we need the data in JS)
            // OR we can just use the DOM since we built the inputs.
            // Problem: We need the VALUES of these items for each payroll.
            // We can pass the full data structure to JS.

            const payrollsData = @json($payrollsData);

            payrollsData.forEach(payroll => {
                // Fill Incomes
                payroll.incomes.forEach(item => {
                    // Find input matching payrollID, Group=income, Type, Desc
                    // Since we don't know the UUID, we must find the input by data attributes
                    const input = document.querySelector(
                        `input[data-payroll-id="${payroll.id}"][data-group="income"][data-type="${item.income_type}"][data-desc="${item.description}"]`
                    );
                    if (input) {
                        input.value = item.amount;
                    }
                });

                // Fill Deductions
                payroll.deductions.forEach(item => {
                    const input = document.querySelector(
                        `input[data-payroll-id="${payroll.id}"][data-group="deduction"][data-type="${item.deduction_type}"][data-desc="${item.description}"]`
                        );
                    if (input) {
                        // If Late Deduction, use rate (minutes), else use amount
                        if (item.deduction_type === 'late' && item.rate > 0) {
                            input.value = item.rate;
                        } else {
                            input.value = item.amount;
                        }
                    }
                });
            });
        }

        function removeColumn(btn, uuid) {
            if (!confirm('ต้องการลบคอลัมน์นี้หรือไม่? ข้อมูลที่กรอกไว้ในคอลัมน์นี้จะหายไป')) return;

            // Remove Definition
            const defs = document.querySelectorAll(`input[name*="[${uuid}]"]`);
            defs.forEach(el => el.remove());

            // Remove TDs
            const tds = document.querySelectorAll(`.dynamic-col-${uuid}`);
            tds.forEach(td => td.remove());

            // Remove TH
            const th = btn.closest('th');
            th.remove();
        }
    </script>
@endsection
