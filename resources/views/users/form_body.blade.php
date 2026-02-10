@php
    $user = $user ?? null;
    $is_edit = !is_null($user);
    $prefix = $is_edit ? 'edit_' : 'add_';
@endphp

<ul class="nav nav-tabs" id="userTabs{{ $prefix }}" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="{{ $prefix }}general-tab" data-bs-toggle="tab"
            data-bs-target="#{{ $prefix }}general" type="button" role="tab"
            aria-controls="{{ $prefix }}general" aria-selected="true">ข้อมูลทั่วไป</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="{{ $prefix }}personal-tab" data-bs-toggle="tab"
            data-bs-target="#{{ $prefix }}personal" type="button" role="tab"
            aria-controls="{{ $prefix }}personal" aria-selected="false">ข้อมูลส่วนตัว</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="{{ $prefix }}employment-tab" data-bs-toggle="tab"
            data-bs-target="#{{ $prefix }}employment" type="button" role="tab"
            aria-controls="{{ $prefix }}employment" aria-selected="false">การจ้างงาน</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="{{ $prefix }}payroll-tab" data-bs-toggle="tab"
            data-bs-target="#{{ $prefix }}payroll" type="button" role="tab"
            aria-controls="{{ $prefix }}payroll" aria-selected="false">ข้อมูลเงินเดือน/ธนาคาร</button>
    </li>
</ul>

<div class="tab-content pt-3" id="userTabsContent{{ $prefix }}">
    <!-- General Info -->
    <div class="tab-pane fade show active" id="{{ $prefix }}general" role="tabpanel"
        aria-labelledby="{{ $prefix }}general-tab">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}code" class="form-label">รหัสพนักงาน <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" name="code" id="{{ $prefix }}code"
                    value="{{ $user->code ?? '' }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}name" class="form-label">ชื่อ-นามสกุล <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="{{ $prefix }}name"
                    value="{{ $user->name ?? '' }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}email" class="form-label">Email <span
                        class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="{{ $prefix }}email"
                    value="{{ $user->email ?? '' }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}level" class="form-label">บทบาท (Role) <span
                        class="text-danger">*</span></label>
                <select class="form-control" id="{{ $prefix }}level" name="level" required>
                    <option value="">-- เลือกบทบาท --</option>
                    <option value="0" {{ ($user->level ?? '') == '0' ? 'selected' : '' }}>Owner</option>
                    <option value="1" {{ ($user->level ?? '') == '1' ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ ($user->level ?? '') == '2' ? 'selected' : '' }}>Teacher</option>
                    <option value="3" {{ ($user->level ?? '') == '3' ? 'selected' : '' }}>Account</option>
                    <option value="4" {{ ($user->level ?? '') == '4' ? 'selected' : '' }}>Officer</option>
                    <option value="5" {{ ($user->level ?? '') == '5' ? 'selected' : '' }}>Normal (Employee)
                    </option>
                    <option value="7" {{ ($user->level ?? '') == '7' ? 'selected' : '' }}>Finance</option>
                    <option value="8" {{ ($user->level ?? '') == '8' ? 'selected' : '' }}>Header</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}password" class="form-label">Password
                    {{ $is_edit ? '(เว้นว่างหากไม่เปลี่ยน)' : '' }} <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password" id="{{ $prefix }}password"
                    autocomplete="new-password" {{ $is_edit ? '' : 'required' }}>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" name="password_confirmation"
                    id="{{ $prefix }}password_confirmation" autocomplete="new-password"
                    {{ $is_edit ? '' : 'required' }}>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}type_user" class="form-label">เวลาเข้างาน</label>
                <select class="form-control" id="{{ $prefix }}type_user" name="type_user">
                    <option value="" selected>Select Time</option>
                    <option value="0" {{ ($user->type_user ?? '') == '0' ? 'selected' : '' }}>10.00-18.00
                    </option>
                    <option value="1" {{ ($user->type_user ?? '') == '1' ? 'selected' : '' }}>09.00-17.00
                    </option>
                </select>
            </div>
        </div>
    </div>

    <!-- Personal Info -->
    <div class="tab-pane fade" id="{{ $prefix }}personal" role="tabpanel"
        aria-labelledby="{{ $prefix }}personal-tab">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}title_prefix" class="form-label">คำนำหน้า</label>
                <select class="form-control" name="title_prefix" id="{{ $prefix }}title_prefix">
                    <option value="">-- เลือก --</option>
                    @foreach (\App\Models\User::TITLE_PREFIXES as $key => $label)
                        <option value="{{ $key }}"
                            {{ ($user->title_prefix ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}nickname" class="form-label">ชื่อเล่น</label>
                <input type="text" class="form-control" name="nickname" id="{{ $prefix }}nickname"
                    value="{{ $user->nickname ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}gender" class="form-label">เพศ</label>
                <select class="form-control" name="gender" id="{{ $prefix }}gender">
                    <option value="">-- เลือก --</option>
                    <option value="male" {{ ($user->gender ?? '') == 'male' ? 'selected' : '' }}>ชาย</option>
                    <option value="female" {{ ($user->gender ?? '') == 'female' ? 'selected' : '' }}>หญิง</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}birth_date" class="form-label">วันเกิด</label>
                <input type="text" class="form-control datepicker" name="birth_date"
                    id="{{ $prefix }}birth_date"
                    value="{{ isset($user->birth_date) ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}nationality" class="form-label">สัญชาติ</label>
                <input type="text" class="form-control" name="nationality" id="{{ $prefix }}nationality"
                    value="{{ $user->nationality ?? 'ไทย' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}phone" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" name="phone" id="{{ $prefix }}phone"
                    value="{{ $user->phone ?? '' }}">
            </div>
            <div class="col-md-12 mb-3">
                <label for="{{ $prefix }}address" class="form-label">ที่อยู่</label>
                <textarea class="form-control" name="address" id="{{ $prefix }}address" rows="2">{{ $user->address ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <!-- Employment Info -->
    <div class="tab-pane fade" id="{{ $prefix }}employment" role="tabpanel"
        aria-labelledby="{{ $prefix }}employment-tab">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}position" class="form-label">ตำแหน่ง</label>
                <input type="text" class="form-control" name="position" id="{{ $prefix }}position"
                    value="{{ $user->position ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}department" class="form-label">แผนก</label>
                <input type="text" class="form-control" name="department" id="{{ $prefix }}department"
                    value="{{ $user->department ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}employee_type" class="form-label">ประเภทพนักงาน</label>
                <select class="form-control" name="employee_type" id="{{ $prefix }}employee_type">
                    <option value="fulltime"
                        {{ ($user->employee_type ?? 'fulltime') == 'fulltime' ? 'selected' : '' }}>Full-time</option>
                    <option value="parttime" {{ ($user->employee_type ?? '') == 'parttime' ? 'selected' : '' }}>
                        Part-time</option>
                    <option value="contract" {{ ($user->employee_type ?? '') == 'contract' ? 'selected' : '' }}>
                        Contract</option>
                </select>
            </div>
            @can('users.edit.detail')
                <div class="col-md-6 mb-3">
                    <label for="{{ $prefix }}start_date" class="form-label">วันที่เริ่มงาน</label>
                    <input type="text" class="form-control datepicker" name="start_date"
                        id="{{ $prefix }}start_date"
                        value="{{ isset($user->start_date) ? \Carbon\Carbon::parse($user->start_date)->format('Y-m-d') : '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="{{ $prefix }}end_date" class="form-label">วันที่สิ้นสุดงาน</label>
                    <input type="text" class="form-control datepicker" name="end_date"
                        id="{{ $prefix }}end_date"
                        value="{{ isset($user->end_date) ? \Carbon\Carbon::parse($user->end_date)->format('Y-m-d') : '' }}">
                </div>

                {{-- Probation Section --}}
                <div class="col-md-6 mb-3 d-flex align-items-center">
                    <div class="form-check form-switch ps-0">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="{{ $prefix }}is_probation"
                            name="is_probation" value="1" {{ $user->is_probation ?? false ? 'checked' : '' }}
                            onchange="toggleProbationFields('{{ $prefix }}')" style="float: none;">
                        <label class="form-check-label" for="{{ $prefix }}is_probation">อยู่ในช่วงทดลองงาน</label>
                    </div>
                </div>

                <div class="col-md-6 mb-3 probation-field" id="{{ $prefix }}probation_end_date_wrapper"
                    style="display: {{ $user->is_probation ?? false ? 'block' : 'none' }};">
                    <label for="{{ $prefix }}probation_end_date" class="form-label">วันสิ้นสุดการทดลองงาน</label>
                    <input type="text" class="form-control datepicker" name="probation_end_date"
                        id="{{ $prefix }}probation_end_date"
                        value="{{ isset($user->probation_end_date) ? \Carbon\Carbon::parse($user->probation_end_date)->format('Y-m-d') : '' }}">
                </div>
            @endcan

            <div class="col-md-6 mb-3">
                <label class="form-label">ความถี่การจ่ายเงินเดือน</label>
                <div class="mt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payroll_frequency"
                            id="{{ $prefix }}freq_monthly" value="monthly"
                            {{ ($user->payroll_frequency ?? 'monthly') == 'monthly' ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}freq_monthly">รายเดือน</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payroll_frequency"
                            id="{{ $prefix }}freq_weekly" value="weekly"
                            {{ ($user->payroll_frequency ?? 'monthly') == 'weekly' ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}freq_weekly">รายสัปดาห์</label>
                    </div>
                </div>
            </div>

            {{-- Tags Section --}}
            <div class="col-md-12 mb-3">
                <label class="form-label d-block mb-2"><i class="fas fa-tags me-1"></i>Tags (เช่น วิชาที่สอน)</label>
                <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light">
                    @php
                        $all_tags = \App\Models\UserTag::active()->orderBy('name')->get();
                        $user_tag_ids = $user?->tags?->pluck('id')->toArray() ?? [];
                    @endphp
                    @foreach ($all_tags as $tag)
                        <div class="position-relative">
                            <input type="checkbox" class="btn-check" name="tags[]"
                                id="{{ $prefix }}tag_{{ $tag->id }}" value="{{ $tag->id }}"
                                {{ in_array($tag->id, $user_tag_ids) ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-{{ $tag->color }} btn-sm rounded-pill"
                                for="{{ $prefix }}tag_{{ $tag->id }}">
                                {{ $tag->name }}
                                <i class="fas fa-check check-icon d-none"></i>
                            </label>
                        </div>
                    @endforeach

                    <a href="{{ route('user-tags.index') }}" target="_blank"
                        class="btn btn-sm btn-secondary rounded-pill" title="จัดการ Tags">
                        <i class="fas fa-cog"></i> จัดการ Tags
                    </a>
                </div>
                <style>
                    .btn-check:checked+.btn .check-icon {
                        display: inline-block !important;
                        margin-left: 5px;
                    }
                </style>
            </div>
        </div>
    </div>

    <!-- Payroll Info -->
    <div class="tab-pane fade" id="{{ $prefix }}payroll" role="tabpanel"
        aria-labelledby="{{ $prefix }}payroll-tab">
        <div class="row">
            @can('users.edit.salary')
                <div class="col-md-6 mb-3">
                    <label for="{{ $prefix }}base_salary" class="form-label">เงินเดือนฐาน</label>
                    <input type="number" step="0.01" class="form-control" name="base_salary"
                        id="{{ $prefix }}base_salary" value="{{ $user->base_salary ?? 0 }}">
                </div>
            @endcan
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}bank_name" class="form-label">ธนาคาร</label>
                <input type="text" class="form-control" name="bank_name" id="{{ $prefix }}bank_name"
                    value="{{ $user->bank_name ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}bank_account" class="form-label">เลขที่บัญชี</label>
                <input type="text" class="form-control" name="bank_account"
                    id="{{ $prefix }}bank_account" value="{{ $user->bank_account ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}tax_id" class="form-label">เลขประจำตัวผู้เสียภาษี</label>
                <input type="text" class="form-control" name="tax_id" id="{{ $prefix }}tax_id"
                    value="{{ $user->tax_id ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}social_security_no" class="form-label">เลขประกันสังคม</label>
                <input type="text" class="form-control" name="social_security_no"
                    id="{{ $prefix }}social_security_no" value="{{ $user->social_security_no ?? '' }}">
            </div>

            {{-- Salary Items สำหรับ Part-time --}}
            <div class="col-12 mt-3" id="{{ $prefix }}salary_items_section">
                <div class="card border-info">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list me-1"></i>รายการรายได้ (Part-time)</h6>
                        <button type="button" class="btn btn-sm btn-light"
                            onclick="addSalaryItem('{{ $prefix }}')">
                            <i class="fas fa-plus me-1"></i>เพิ่มรายการ
                        </button>
                    </div>
                    <div class="card-body p-2" id="{{ $prefix }}salary_items_container">
                        @php
                            $salary_items = $user?->salaryItems ?? collect();
                        @endphp
                        @forelse($salary_items as $index => $item)
                            <div class="row g-2 mb-2 salary-item-row"
                                id="{{ $prefix }}salary_item_{{ $item->id }}">
                                <div class="col-5">
                                    <input type="text" class="form-control form-control-sm"
                                        name="salary_items[{{ $item->id }}][description]"
                                        value="{{ $item->description }}" placeholder="ชื่อรายการ">
                                    <input type="hidden" name="salary_items[{{ $item->id }}][id]"
                                        value="{{ $item->id }}">
                                </div>
                                <div class="col-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" class="form-control"
                                            name="salary_items[{{ $item->id }}][rate]"
                                            value="{{ $item->rate }}" placeholder="เรท">
                                        <span class="input-group-text">฿</span>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <select class="form-select form-select-sm"
                                        name="salary_items[{{ $item->id }}][unit]">
                                        @foreach (\App\Models\UserSalaryItem::UNITS as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ $item->unit == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeSalaryItem('{{ $prefix }}salary_item_{{ $item->id }}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0 empty-message"
                                id="{{ $prefix }}empty_salary_msg">
                                ยังไม่มีรายการ กดปุ่ม "เพิ่มรายการ" เพื่อเพิ่ม
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let salaryItemCounter = {{ $salary_items->count() }};

    function addSalaryItem(prefix) {
        const container = document.getElementById(prefix + 'salary_items_container');
        const emptyMsg = document.getElementById(prefix + 'empty_salary_msg');
        if (emptyMsg) emptyMsg.remove();

        const newIndex = 'new_' + (++salaryItemCounter);
        const html = `
            <div class="row g-2 mb-2 salary-item-row" id="${prefix}salary_item_${newIndex}">
                <div class="col-5">
                    <input type="text" class="form-control form-control-sm"
                        name="salary_items[${newIndex}][description]"
                        placeholder="ชื่อรายการ เช่น สอนคณิต ป.6" required>
                </div>
                <div class="col-3">
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" class="form-control"
                            name="salary_items[${newIndex}][rate]"
                            placeholder="เรท" required>
                        <span class="input-group-text">฿</span>
                    </div>
                </div>
                <div class="col-2">
                    <select class="form-select form-select-sm" name="salary_items[${newIndex}][unit]">
                        <option value="ชม.">ชั่วโมง</option>
                        <option value="วัน">วัน</option>
                        <option value="ครั้ง">ครั้ง</option>
                    </select>
                </div>
                <div class="col-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="removeSalaryItem('${prefix}salary_item_${newIndex}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function toggleProbationFields(prefix) {
        const isProbation = document.getElementById(prefix + 'is_probation').checked;
        const endDateWrapper = document.getElementById(prefix + 'probation_end_date_wrapper');
        const endDateInput = document.getElementById(prefix + 'probation_end_date');

        if (isProbation) {
            endDateWrapper.style.display = 'block';
            endDateInput.required = true;
        } else {
            endDateWrapper.style.display = 'none';
            endDateInput.required = false;
            endDateInput.value = '';
        }
    }

    function removeSalaryItem(rowId) {
        const row = document.getElementById(rowId);
        if (row) row.remove();
    }
</script>
