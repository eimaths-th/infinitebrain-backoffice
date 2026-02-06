@extends('layouts.main_template')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>สร้างงวดเงินเดือนใหม่</h5>
                    </div>
                    <div class="card-body">
                        @if ($existing_periods->count() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>งวดประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$current_month] }}
                                    {{ $current_year }} ที่มีอยู่แล้ว:</strong>
                                <ul class="mt-2 mb-0">
                                    @foreach ($existing_periods as $ex)
                                        <li>
                                            <span class="badge bg-secondary">{{ $ex->period_code }}</span>
                                            {{ $ex->period_name }}
                                            ({!! $ex->GetStatusBadge() !!})
                                            <a href="{{ route('payroll.show', $ex->id) }}" class="ms-2">ดูข้อมูล</a>
                                        </li>
                                    @endforeach
                                </ul>
                                <hr>
                                <small>ท่านสามารถสร้างงวดเพิ่มได้ (เช่น งวดรายสัปดาห์ใหม่) โดยระบุข้อมูลด้านล่าง</small>
                            </div>
                        @endif

                        <form action="{{ route('payroll.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">ปี (พ.ศ.)</label>
                                <select name="year" class="form-select @error('year') is-invalid @enderror" required>
                                    @for ($y = $current_year + 1; $y >= $current_year - 2; $y--)
                                        <option value="{{ $y }}" {{ $y == $current_year ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">เดือน</label>
                                <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                    @foreach (\App\Models\PayrollPeriod::$thaiMonths as $m => $name)
                                        <option value="{{ $m }}" {{ $m == $current_month ? 'selected' : '' }}>
                                            {{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ประเภทงวด</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="frequency" id="freq_monthly"
                                            value="monthly" checked onchange="toggleFrequency()">
                                        <label class="form-check-label" for="freq_monthly">รายเดือน</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="frequency" id="freq_weekly"
                                            value="weekly" onchange="toggleFrequency()">
                                        <label class="form-check-label" for="freq_weekly">รายสัปดาห์</label>
                                    </div>
                                </div>
                            </div>

                            <div id="weekly_dates" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">วันที่เริ่ม <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" id="start_date" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" id="end_date" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <script>
                                function toggleFrequency() {
                                    const isWeekly = document.getElementById('freq_weekly').checked;
                                    const weeklyDiv = document.getElementById('weekly_dates');
                                    const startDate = document.getElementById('start_date');
                                    const endDate = document.getElementById('end_date');

                                    if (isWeekly) {
                                        weeklyDiv.style.display = 'block';
                                        startDate.required = true;
                                        endDate.required = true;
                                    } else {
                                        weeklyDiv.style.display = 'none';
                                        startDate.required = false;
                                        endDate.required = false;
                                    }
                                }
                            </script>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> สร้างงวด
                                </button>
                                <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> กลับ
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
