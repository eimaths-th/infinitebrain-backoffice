@extends('layouts.main_template')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">นำเข้าข้อมูลจาก Excel (Import Bank Statement)</h4>
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

                    <div class="alert alert-info">
                        <strong>คำแนะนำ:</strong> กรุณาใช้ไฟล์ Excel ที่มีโครงสร้างถูกต้องตามแบบฟอร์ม
                    </div>

                    <form action="{{ route('bank-statement.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group text-center p-5 border rounded bg-light">
                            <label for="file" class="h5 d-block mb-3">เลือกไฟล์ Excel (.xlsx, .xls)</label>
                            <input type="file" name="file" id="file" class="form-control-file d-none"
                                onchange="document.getElementById('file-name').textContent = this.files[0].name" required>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="document.getElementById('file').click()">
                                <i class="feather icon-folder"></i> Browse File
                            </button>
                            <p id="file-name" class="mt-2 text-muted">ไม่ได้เลือกไฟล์</p>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('bank-statement.index') }}" class="btn btn-secondary">ยกเลิก</a>
                            <button type="submit" class="btn btn-success px-5">
                                <i class="feather icon-upload-cloud"></i> เริ่มนำเข้า (Import)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
