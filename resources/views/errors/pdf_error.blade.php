<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ไม่สามารถสร้าง PDF ได้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> ไม่สามารถสร้าง PDF ได้</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i> {{ $message }}
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i> วิธีแก้ไข</h5>
                            <p>กรุณาตรวจสอบว่าได้ติดตั้ง LibreOffice บน Server แล้ว:</p>
                            <ol>
                                <li>ติดตั้ง LibreOffice: <code>sudo apt-get install libreoffice -y</code></li>
                                <li>ตรวจสอบ path: <code>which soffice</code></li>
                                <li>เพิ่มใน .env: <code>LIBREOFFICE_PATH=/usr/bin/soffice</code></li>
                                <li>Clear config: <code>php artisan config:clear</code></li>
                            </ol>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('leave-requests.show', $leave_request_id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> กลับไปหน้าใบลา
                            </a>
                            <a href="{{ route('leave-requests.export', $leave_request_id) }}" class="btn btn-success">
                                <i class="fas fa-file-word me-1"></i> ดาวน์โหลด Word แทน
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
