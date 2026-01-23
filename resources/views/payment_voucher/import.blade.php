@extends('layouts.main_template')

@section('style')
    <style>
        .import-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .import-card h2 {
            margin-bottom: 10px;
        }

        .upload-zone {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-zone:hover {
            background: #e9ecef;
            border-color: #0056b3;
        }

        .upload-zone.dragover {
            background: #cce5ff;
            border-color: #004085;
        }

        .upload-zone i {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 15px;
        }

        .file-info {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: #d4edda;
            border-radius: 8px;
            color: #155724;
        }

        .file-info.show {
            display: block;
        }

        .instructions {
            background: #fff3cd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .instructions h5 {
            color: #856404;
        }

        .instructions ul {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <p class="mb-0">{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <p class="mb-0">{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <p class="mb-0"><i class="fa fa-exclamation-circle"></i> กรุณาตรวจสอบข้อมูล:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-upload icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        Import ใบสำคัญจ่าย
                        <div class="page-title-subheading">นำเข้าข้อมูลใบสำคัญจ่ายจากไฟล์ Excel</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('payment-voucher.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                    <a href="{{ route('payment-voucher.import.template') }}" class="btn btn-success">
                        <i class="fa fa-download"></i> ดาวน์โหลด Template
                    </a>
                </div>
            </div>
        </div>

        <div class="import-card">
            <h2><i class="fa fa-file-excel-o"></i> Import Excel</h2>
            <p>อัพโหลดไฟล์ Excel เพื่อนำเข้าข้อมูลใบสำคัญจ่ายอัตโนมัติ</p>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <form action="{{ route('payment-voucher.import.process') }}" method="POST" enctype="multipart/form-data"
                    id="importForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">สาขา <span class="text-danger">*</span></label>
                            <select name="branch_code" class="form-select" required>
                                @foreach ($branchCodes as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="upload-zone" id="uploadZone">
                        <i class="fa fa-cloud-upload"></i>
                        <h4>ลากไฟล์มาวางที่นี่</h4>
                        <p class="text-muted">หรือคลิกเพื่อเลือกไฟล์</p>
                        <p class="text-muted mb-0"><small>รองรับไฟล์ .xlsx, .xls ขนาดไม่เกิน 10MB</small></p>
                        <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls" hidden required>
                    </div>

                    <div class="file-info" id="fileInfo">
                        <i class="fa fa-check-circle"></i>
                        <span id="fileName"></span>
                        <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="clearFile()">
                            <i class="fa fa-times"></i> ยกเลิก
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fa fa-upload"></i> Import ข้อมูล
                        </button>
                    </div>
                </form>

                <div class="instructions">
                    <h5><i class="fa fa-info-circle"></i> คำแนะนำการใช้งาน</h5>
                    <ul>
                        <li>ใช้ไฟล์ Template ที่ดาวน์โหลดได้จากปุ่มด้านบน</li>
                        <li>โครงสร้างไฟล์:
                            <ul>
                                <li><strong>Cell J10</strong>: เลขที่ใบสำคัญจ่าย</li>
                                <li><strong>Cell C10</strong>: ชื่อผู้รับเงิน</li>
                                <li><strong>Cell J11</strong>: วันที่ (รูปแบบ dd/mm/yyyy)</li>
                                <li><strong>Cell C12</strong>: เลขที่บัญชี + ธนาคาร</li>
                                <li><strong>Cell C14</strong>: ที่อยู่</li>
                                <li><strong>Row 19-31</strong>: รายการ (Col A=ลำดับ, Col C=รายการ, Col I=จำนวนเงิน)</li>
                            </ul>
                        </li>
                        <li>ระบบจะตรวจสอบเลขที่ใบสำคัญจ่ายไม่ให้ซ้ำ</li>
                        <li>ใบสำคัญจ่ายที่ Import จะมีสถานะ "ร่าง" รอการตรวจสอบ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            const uploadZone = document.getElementById('uploadZone');
            const fileInput = document.getElementById('excelFile');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const submitBtn = document.getElementById('submitBtn');

            // Click to upload
            uploadZone.addEventListener('click', () => fileInput.click());

            // Drag and drop
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('dragover');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFile(files[0]);
                }
            });

            // File input change
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFile(e.target.files[0]);
                }
            });

            function handleFile(file) {
                // Validate file type
                const validTypes = ['.xlsx', '.xls'];
                const ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

                if (!validTypes.includes(ext)) {
                    alert('กรุณาเลือกไฟล์ Excel (.xlsx หรือ .xls)');
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 10MB)');
                    return;
                }

                fileName.textContent = file.name + ' (' + formatBytes(file.size) + ')';
                fileInfo.classList.add('show');
                uploadZone.style.display = 'none';
                submitBtn.disabled = false;
            }

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });

        function clearFile() {
            document.getElementById('excelFile').value = '';
            document.getElementById('fileInfo').classList.remove('show');
            document.getElementById('uploadZone').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
        }
    </script>
@endsection
