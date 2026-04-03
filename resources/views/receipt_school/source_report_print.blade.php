<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปแหล่งที่มาลูกค้า - Infinite Brain</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            color: #333;
            background-color: #fff;
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }

        .report-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10px;
        }

        /* Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .logo-section img {
            height: 65px;
            margin-bottom: 5px;
        }

        .company-info {
            text-align: right;
            font-size: 12px;
            line-height: 1.4;
        }

        /* Title Area */
        .report-title-area {
            text-align: center;
            margin-bottom: 30px;
        }

        .report-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #000;
        }

        .report-subtitle {
            font-size: 14px;
            color: #666;
        }

        /* Summary Table */
        .summary-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            border-left: 4px solid #333;
            padding-left: 10px;
        }

        .table-summary {
            width: 50%;
            margin-bottom: 0;
        }

        /* Main Table */
        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .table-report th {
            background-color: #f8f9fa !important;
            color: #000;
            font-weight: 600;
            text-align: center;
            border: 1px solid #333 !important;
            padding: 10px 5px;
            -webkit-print-color-adjust: exact;
        }

        .table-report td {
            border: 1px solid #333 !important;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .text-center { text-align: center !important; }

        /* Signature Area */
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 30px;
        }

        .signature-title {
            font-size: 13px;
        }

        /* Utility */
        .badge-source {
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #eee;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="no-print text-center py-3 bg-light border-bottom mb-4">
        <button class="btn btn-primary px-4 shadow-sm" onclick="window.print()">
            <i class="fa fa-print"></i> คลิกเพื่อสั่งพิมพ์ (Print Now)
        </button>
        <button class="btn btn-secondary px-4 ms-2 shadow-sm" onclick="window.close()">
            ปิดหน้าต่างนี้ (Close)
        </button>
    </div>

    <div class="report-container">
        <!-- Header -->
        <header class="report-header">
            <div class="logo-section">
                <img src="{{ asset('images/logo_infinitebrain.png') }}" alt="Infinite Brain Logo">
                <div class="report-subtitle">สถาบันกวดวิชา อินฟินิท เบรน (Infinite Brain)</div>
            </div>
            <div class="company-info">
                <strong>บริษัท อินฟินิท เอดดูเคชั่น กรุ๊ป จำกัด</strong><br>
                รายงานระบบใบเสร็จรับเงินโรงเรียน (School Receipt System)<br>
                พิมพ์เมื่อ: {{ date('d/m/Y H:i') }}<br>
                โดย: {{ Auth::user()->name }}
            </div>
        </header>

        <!-- Title -->
        <div class="report-title-area">
            <h1 class="report-title">รายงานสรุปแหล่งที่มาลูกค้า (Customer Source Report)</h1>
            <div class="report-subtitle">
                เงื่อนไข: แหล่งที่มา = {{ $source_filter ? $source_filter : 'ทั้งหมด' }}
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <h2 class="section-title">สรุปข้อมูล (Summary)</h2>
            <table class="table table-bordered table-summary">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">แหล่งที่มา (Source)</th>
                        <th class="text-center" width="150">จำนวน (รายการ)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grand_total = 0; @endphp
                    @foreach($summary as $item)
                        <tr>
                            <td>{{ $item->source }}</td>
                            <td class="text-center">{{ number_format($item->total_count) }}</td>
                        </tr>
                        @php $grand_total += $item->total_count; @endphp
                    @endforeach
                    <tr class="fw-bold table-light">
                        <td class="text-end">รวมทั้งสิ้น</td>
                        <td class="text-center">{{ number_format($grand_total) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Detail Table -->
        <div class="detail-section">
            <h2 class="section-title">รายละเอียดข้อมูล (Detailed Records)</h2>
            <table class="table-report">
                <thead>
                    <tr>
                        <th width="40">ลำดับ</th>
                        <th width="100">วันที่ทำรายการ</th>
                        <th width="120">เลขที่ใบเสร็จ</th>
                        <th>ชื่อผู้ปกครอง</th>
                        <th>ชื่อนักเรียน</th>
                        <th width="120">แหล่งที่มา</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ date('d/m/Y', strtotime($row->submission_date)) }}</td>
                            <td class="text-center fw-bold">{{ $row->receipt_number }}</td>
                            <td>{{ $row->name_parent }}</td>
                            <td>
                                @php
                                    $studentNames = [];
                                    $ids = explode('|', $row->pid);
                                    foreach ($ids as $id) {
                                        if (isset($students[$id])) {
                                            $studentNames[] = $students[$id]->nick_name;
                                        } elseif (!empty($id)) {
                                            $studentNames[] = '-';
                                        }
                                    }
                                    echo !empty($studentNames) ? implode(', ', $studentNames) : '-';
                                @endphp
                            </td>
                            <td class="text-center">
                                <span class="badge-source">{{ $row->source }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูลที่ตรงกับเงื่อนไข</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">ผู้จัดทำรายงาน / (Prepared By)</div>
                <div class="mt-2 small text-muted">( {{ Auth::user()->name }} )</div>
                <div class="small text-muted">วันที่: {{ date('d/m/Y') }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">ผู้ตรวจสอบ / (Verified By)</div>
                <div class="mt-2 small text-muted">( ...................................................... )</div>
                <div class="small text-muted">วันที่: ......../......../........</div>
            </div>
        </div>
    </div>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <script>
        // สั่งพิมพ์อัตโนมัติเมื่อโหลดหน้า (ถ้าผู้ใช้ต้องการ)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
