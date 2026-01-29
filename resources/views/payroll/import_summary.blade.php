@extends('layouts.main_template')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-file-invoice me-2"></i>ตรวจสอบข้อมูล Payslip
                            </h5>
                            <p class="text-muted mb-0 mt-1">งวด: {{ $period->period_name }}</p>
                        </div>
                        <a href="{{ route('payroll.import', $period->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-upload me-1"></i> เลือกไฟล์ใหม่
                        </a>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
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

                        @if (count($preview_data) == 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ไม่พบข้อมูลใน Sheet Payslip
                            </div>
                            <a href="{{ route('payroll.import', $period->id) }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i> กลับ
                            </a>
                        @else
                            <form action="{{ route('payroll.import.process', $period->id) }}" method="POST">
                                @csrf

                                <div class="alert alert-info mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-info-circle me-2"></i>
                                        พบข้อมูล {{ count($preview_data) }} รายการ
                                    </div>
                                    <div class="fw-bold">
                                        รวมจ่ายสุทธิ:
                                        {{ number_format(collect($preview_data)->sum(fn($item) => $item['total_income'] - $item['total_deduction']), 2) }}
                                        บาท
                                    </div>
                                </div>

                                <div class="row">
                                    @foreach ($preview_data as $index => $row)
                                        @php
                                            $user = \App\Models\User::where('code', $row['code'])->first();
                                            $has_existing =
                                                $user &&
                                                \App\Models\Payroll::where('payroll_period_id', $period->id)
                                                    ->where('user_id', $user->id)
                                                    ->exists();

                                            $net_pay = $row['total_income'] - $row['total_deduction'];
                                        @endphp

                                        <div class="col-xxl-3 col-xl-4 col-md-6 mb-3">
                                            <div
                                                class="card shadow-sm border-{{ $user ? ($has_existing ? 'info' : 'success') : 'warning' }}">
                                                <div
                                                    class="card-header py-2 px-3 bg-{{ $user ? ($has_existing ? 'info' : 'success') : 'warning' }} text-white d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="selected[{{ $row['code'] }}]" value="1"
                                                                {{ $user ? 'checked' : '' }}
                                                                id="check_{{ $index }}">
                                                            <label class="form-check-label fw-bold text-truncate d-block"
                                                                for="check_{{ $index }}" style="max-width: 150px;">
                                                                {{ $row['name'] }}
                                                            </label>
                                                        </div>
                                                        <small class="d-block text-white-50"
                                                            style="font-size: 0.75rem;">{{ $row['code'] }} |
                                                            {{ $row['position'] }}</small>
                                                    </div>
                                                    <span
                                                        class="badge bg-white text-dark">{{ number_format($net_pay, 2) }}</span>
                                                </div>
                                                <div class="card-body p-2">
                                                    @if (!$user)
                                                        <div class="mb-2">
                                                            <select name="user_mapping[{{ $row['code'] }}]"
                                                                class="form-select form-select-sm border-warning bg-light">
                                                                <option value="">➕ สร้าง User ใหม่ (New)</option>
                                                                <optgroup label="จับคู่กับ User ที่มีอยู่">
                                                                    @foreach ($all_users as $u)
                                                                        <option value="{{ $u->id }}">
                                                                            {{ $u->name }} ({{ $u->code }})
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            </select>
                                                        </div>
                                                    @elseif($has_existing)
                                                        <div class="alert alert-info py-1 px-2 mb-2"
                                                            style="font-size: 0.75rem;">
                                                            <i class="fas fa-info-circle me-1"></i> จะอัปเดตข้อมูลเดิม
                                                        </div>
                                                    @endif

                                                    <div class="row g-0" style="font-size: 0.8rem;">
                                                        <div class="col-6 border-end pe-2">
                                                            <h6 class="text-success border-bottom pb-1 mb-1"
                                                                style="font-size: 0.85rem;">รายได้</h6>
                                                            @foreach ($row['income_items'] as $item)
                                                                <div class="d-flex justify-content-between mb-0">
                                                                    <span class="text-truncate me-1"
                                                                        title="{{ $item['description'] }}">{{ $item['description'] }}</span>
                                                                    <span>{{ number_format($item['amount'], 2) }}</span>
                                                                </div>
                                                            @endforeach
                                                            <div
                                                                class="d-flex justify-content-between fw-bold border-top pt-1 mt-1 text-success">
                                                                <span>รวม</span>
                                                                <span>{{ number_format($row['total_income'], 2) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 ps-2">
                                                            <h6 class="text-danger border-bottom pb-1 mb-1"
                                                                style="font-size: 0.85rem;">รายการหัก</h6>
                                                            @foreach ($row['deduction_items'] as $item)
                                                                <div class="d-flex justify-content-between mb-0">
                                                                    <span class="text-truncate me-1"
                                                                        title="{{ $item['description'] }}">{{ $item['description'] }}</span>
                                                                    <span>{{ number_format($item['amount'], 2) }}</span>
                                                                </div>
                                                            @endforeach
                                                            <div
                                                                class="d-flex justify-content-between fw-bold border-top pt-1 mt-1 text-danger">
                                                                <span>รวม</span>
                                                                <span>{{ number_format($row['total_deduction'], 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden Inputs -->
                                    @endforeach
                                </div>

                                <div class="card-footer text-end sticky-bottom bg-white border-top shadow-sm">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-file-import me-2"></i> ยืนยัน Import ข้อมูล
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
