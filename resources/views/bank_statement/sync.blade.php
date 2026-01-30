@extends('layouts.main_template')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">เชื่อมต่อข้อมูลเงินเดือน (Sync Payroll)</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('bank-statement.sync.process') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="payroll_period_id" class="h6">รอบเดือนจ่ายเงิน (Payroll Period)</label>
                            <select name="payroll_period_id" id="payroll_period_id" class="form-control form-control-lg"
                                required>
                                <option value="">-- กรุณาเลือก --</option>
                                @foreach ($periods as $period)
                                    <option value="{{ $period->id }}">
                                        {{ $period->period_name }}
                                        (จ่าย: {{ $period->payment_date ? $period->payment_date->format('d/m/Y') : '-' }})
                                        - {{ number_format($period->total_net_pay, 2) }} บาท
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">แสดงเฉพาะรายการที่ อนุมัติ หรือ จ่ายแล้ว เท่านั้น</small>
                        </div>

                        <div class="form-group mt-4">
                            <label class="h6">รูปแบบการนำเข้า</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="sync_net_pay" name="sync_type" class="custom-control-input"
                                    value="net_pay" checked>
                                <label class="custom-control-label" for="sync_net_pay">ยอดสุทธิ (Net Pay)
                                    เพียงยอดเดียวต่อพนักงาน</label>
                            </div>
                            {{-- Future support for detailed items --}}
                            {{-- <div class="custom-control custom-radio">
                            <input type="radio" id="sync_items" name="sync_type" class="custom-control-input" value="items" disabled>
                            <label class="custom-control-label" for="sync_items">แยกรายการ (เงินเดือน, โอที, หักต่างๆ) - เร็วๆนี้</label>
                        </div> --}}
                        </div>

                        <div class="text-center mt-5">
                            <a href="{{ route('bank-statement.index') }}" class="btn btn-secondary">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="feather icon-refresh-cw"></i> ดึงข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
