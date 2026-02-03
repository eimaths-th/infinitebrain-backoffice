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
                <input type="date" class="form-control" name="birth_date" id="{{ $prefix }}birth_date"
                    value="{{ $user->birth_date ?? '' }}">
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
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}start_date" class="form-label">วันที่เริ่มงาน</label>
                <input type="date" class="form-control" name="start_date" id="{{ $prefix }}start_date"
                    value="{{ $user->start_date ?? '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}end_date" class="form-label">วันที่สิ้นสุดงาน</label>
                <input type="date" class="form-control" name="end_date" id="{{ $prefix }}end_date"
                    value="{{ $user->end_date ?? '' }}">
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
        </div>
    </div>
</div>
