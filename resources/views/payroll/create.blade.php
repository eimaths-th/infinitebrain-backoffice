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
                        @if ($existing)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                งวด {{ \App\Models\PayrollPeriod::$thaiMonths[$current_month] }} {{ $current_year }}
                                มีอยู่แล้ว
                                <a href="{{ route('payroll.show', $existing->id) }}">คลิกเพื่อดู</a>
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
