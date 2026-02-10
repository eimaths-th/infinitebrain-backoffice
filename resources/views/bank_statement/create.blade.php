@extends('layouts.main_template')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">เพิ่มรายการใหม่ (Add New Statement)</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('bank-statement.store') }}" method="POST">
                        @csrf

                        <h5 class="text-primary mb-3">ข้อมูลเบื้องต้น (Basic Info)</h5>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>วันที่ (Date) <span class="text-danger">*</span></label>
                                <input type="text" name="date" class="form-control datepicker"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>เลขที่ (Reference No.)</label>
                                <input type="text" name="reference_no" class="form-control">
                            </div>
                        </div>

                        <h5 class="text-info mb-3 mt-3">รายละเอียด (Details)</h5>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>รายการ (Description)</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>ได้รับจาก (Received From)</label>
                                <input type="text" name="received_from" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>ชำระโดย (Paid By)</label>
                                <input type="text" name="paid_by" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>บัญชีเข้า (Account In)</label>
                                <input type="text" name="account_in" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>บัญชีออก (Account Out)</label>
                                <input type="text" name="account_out" class="form-control">
                            </div>
                        </div>

                        <h5 class="text-success mb-3 mt-3">ยอดเงิน (Amounts)</h5>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>ยอดเงิน (Amount) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control font-weight-bold"
                                    required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>รวมเข้า (Total In)</label>
                                <input type="number" step="0.01" name="total_in" class="form-control text-success">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>รวมออก (Total Out)</label>
                                <input type="number" step="0.01" name="total_out" class="form-control text-danger">
                            </div>
                        </div>

                        <h5 class="text-secondary mb-3 mt-3">เพิ่มเติม (Additional)</h5>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>อายเลเวล (เข้า)</label>
                                <input type="number" step="0.01" name="branch_eye_level_in" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>อายเลเวล (ออก)</label>
                                <input type="number" step="0.01" name="branch_eye_level_out" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>ส่วนกลาง (เข้า)</label>
                                <input type="number" step="0.01" name="branch_central_in" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>ส่วนกลาง (ออก)</label>
                                <input type="number" step="0.01" name="branch_central_out" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>ธรรมเนียม (Fee)</label>
                                <input type="number" step="0.01" name="fee" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>ภาษี (Tax)</label>
                                <input type="number" step="0.01" name="tax" class="form-control">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <a href="{{ route('bank-statement.index') }}" class="btn btn-secondary mr-2">ยกเลิก</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">บันทึกข้อมูล</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
