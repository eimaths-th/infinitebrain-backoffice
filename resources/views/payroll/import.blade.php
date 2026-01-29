@extends('layouts.main_template')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-excel me-2"></i>นำเข้าข้อมูลจาก Excel
                        </h5>
                        <p class="text-muted mb-0 mt-1">งวด: {{ $period->period_name }}</p>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('payroll.import.preview', $period->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label">เลือกไฟล์ Excel</label>
                                <input type="file" name="excel_file"
                                    class="form-control @error('excel_file') is-invalid @enderror" accept=".xlsx,.xls"
                                    required>
                                @error('excel_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">รองรับไฟล์ .xlsx และ .xls</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">รูปแบบการ Import</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check border rounded p-2">
                                            <input class="form-check-input" type="radio" name="import_type"
                                                id="import_summary" value="summary" checked>
                                            <label class="form-check-label" for="import_summary">
                                                <strong><i class="fas fa-table me-1"></i> Sheet สรุป</strong>
                                                <p class="text-muted small mb-0 mt-1">
                                                    Import จาก Sheet แรกที่มีตารางสรุปทุกคน<br>
                                                    (ลำดับ, ชื่อ, ตำแหน่ง, รหัส, เงินเดือน...)
                                                </p>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check border rounded p-2">
                                            <input class="form-check-input" type="radio" name="import_type"
                                                id="import_payslip" value="payslip">
                                            <label class="form-check-label" for="import_payslip">
                                                <strong><i class="fas fa-file-invoice me-1"></i> Sheet Payslip</strong>
                                                <p class="text-muted small mb-0 mt-1">
                                                    Import จาก Sheet ที่มี payslip รายบุคคล<br>
                                                    (รายการรายได้/หัก แยกตามคน)
                                                </p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-secondary" id="format-info">
                                <h6><i class="fas fa-info-circle me-1"></i> ข้อมูลรูปแบบไฟล์</h6>
                                <p class="mb-0 small text-muted">เลือกรูปแบบด้านบน เพื่อดูโครงสร้าง Excel ที่รองรับ</p>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> ตรวจสอบข้อมูล
                                </button>
                                <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-secondary">
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
