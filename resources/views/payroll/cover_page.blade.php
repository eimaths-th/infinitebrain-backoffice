<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบปะหน้า - {{ $period->period_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm;
        }

        * {
            box-sizing: border-box;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        .period-info {
            display: flex;
            justify-content: space-between;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }

        .info-item {
            text-align: center;
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: bold;
            color: #212529;
        }

        .summary-box {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }

        .card {
            flex: 1;
            padding: 20px;
            border-radius: 12px;
            color: #333;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #fff;
        }

        .card.primary {
            border-top: 5px solid #667eea;
        }

        .card.primary .card-value {
            color: #667eea;
        }

        .card.success {
            border-top: 5px solid #11998e;
        }

        .card.success .card-value {
            color: #11998e;
        }

        .card.danger {
            border-top: 5px solid #ff5f6d;
        }

        .card.danger .card-value {
            color: #ff5f6d;
        }

        .card-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-label {
            font-size: 14px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }

        table td.number {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin: 40px 0 10px 0;
        }

        .no-print {
            text-align: center;
            margin: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }

        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        @media print {
            body {
                font-size: 10px;
                line-height: 1.22;
            }

            .no-print {
                display: none;
            }

            .container {
                border: none;
                width: 100%;
                max-width: 100%;
                padding: 0;
            }

            .header {
                padding-bottom: 6px;
                margin-bottom: 6px;
            }

            .company-name {
                font-size: 17px;
                margin-bottom: 2px;
            }

            .document-title {
                font-size: 14px;
                margin-top: 3px;
            }

            .period-info {
                padding: 5px 6px;
                border-radius: 4px;
                margin-bottom: 5px;
            }

            .info-label {
                font-size: 9px;
                margin-bottom: 1px;
            }

            .info-value {
                font-size: 11px;
            }

            .summary-box {
                gap: 6px;
                margin-bottom: 5px;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
                padding: 5px;
                border-radius: 5px;
            }

            .card.primary {
                border-top-width: 3px;
            }

            .card.success {
                border-top-width: 3px;
            }

            .card.danger {
                border-top-width: 3px;
            }

            .card-value {
                font-size: 15px;
                margin-bottom: 1px;
            }

            .card-label {
                font-size: 9px;
            }

            table {
                margin-bottom: 5px;
            }

            table th,
            table td {
                padding: 4px 5px;
            }

            .department-heading {
                margin-top: 5px !important;
                margin-bottom: 3px !important;
                padding: 3px 7px !important;
                border-radius: 3px !important;
                border-left-width: 3px !important;
                font-size: 11px !important;
            }

            .department-count {
                font-size: 9px !important;
            }

            .grand-total-section {
                margin-top: 6px !important;
                padding-top: 4px !important;
            }

            .grand-total-label {
                font-size: 11px !important;
            }

            .grand-total-amount {
                font-size: 12px !important;
            }

            .payroll-note {
                margin-top: 4px !important;
                font-size: 9px !important;
            }

            .footer {
                margin-top: 10px;
            }

            .signature-box {
                width: 160px;
            }

            .signature-line {
                margin: 18px 0 4px 0;
            }

            .signature-date {
                font-size: 9px !important;
                margin-top: 2px !important;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ พิมพ์หน้านี้</button>
        <button class="btn btn-secondary" onclick="window.close()">✕ ปิดหน้าต่าง</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="company-name">บริษัท อินฟินิท เบรน จำกัด (สำนักงานใหญ่)</div>
            <div class="document-title">สรุปรายการจ่ายเงินเดือน (Payroll Summary)</div>
        </div>

        <div class="period-info">
            <div class="info-item">
                <div class="info-label">งวดประจำเดือน</div>
                <div class="info-value">{{ $period->period_name }}
                    @if ($period->frequency == 'weekly')
                        (รายสัปดาห์)
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">ช่วงเวลา</div>
                <div class="info-value">{{ $period->start_date->format('d/m/Y') }} -
                    {{ $period->end_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">วันที่จ่าย</div>
                <div class="info-value">{{ $period->payment_date?->format('d/m/Y') ?? '-' }}</div>
            </div>
        </div>

        <div class="summary-box">
            <div class="card success">
                <div class="card-value">{{ number_format($period->total_income, 2) }}</div>
                <div class="card-label">รายได้รวม</div>
            </div>
            <div class="card danger">
                <div class="card-value">{{ number_format($period->total_deduction, 2) }}</div>
                <div class="card-label">รายการหักรวม</div>
            </div>
            <div class="card primary">
                <div class="card-value">{{ number_format($period->total_net_pay, 2) }}</div>
                <div class="card-label">ยอดจ่ายสุทธิ</div>
            </div>
        </div>

        @foreach ($payrollsByDept as $department => $payrolls)
            <div>
                <div class="department-heading"
                    style="margin-top: 20px; margin-bottom: 10px; font-weight: bold; font-size: 16px; background-color: #e9ecef; padding: 8px 15px; border-radius: 5px; border-left: 5px solid #007bff;">
                    แผนก: {{ $department }} <span class="department-count"
                        style="font-weight: normal; font-size: 14px; color: #666;">({{ count($payrolls) }} ท่าน)</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">ลำดับ</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ตำแหน่ง</th>
                            <th style="text-align: right;">เงินได้สุทธิ</th>
                            <th style="text-align: center;">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payrolls as $index => $payroll)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>{{ $payroll->employee_name }}</td>
                                <td>{{ $payroll->position }}</td>
                                <td class="number">{{ number_format($payroll->net_pay, 2) }}</td>
                                <td style="text-align: center;">
                                    @if ($payroll->status == 'paid')
                                        <span style="color: green;">✓ จ่ายแล้ว</span>
                                    @else
                                        <span style="color: #ffc107;">รอการจ่าย</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f8f9fa;">
                            <td colspan="3" style="text-align: right;">รวมแผนก {{ $department }}</td>
                            <td class="number">{{ number_format($payrolls->sum('net_pay'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        <div style="page-break-inside: avoid;">
            <div class="grand-total-section" style="margin-top: 30px; border-top: 2px solid #333; padding-top: 10px;">
                <table style="width: 100%;">
                    <tr style="font-weight: bold; background-color: #343a40; color: white;">
                        <td class="grand-total-label" colspan="3" style="text-align: right; border: none; font-size: 16px;">รวมทั้งสิ้น
                            ({{ count($period->payrolls) }} ท่าน)</td>
                        <td class="number grand-total-amount" style="border: none; font-size: 18px; width: 150px;">
                            {{ number_format($period->total_net_pay, 2) }}</td>
                        <td style="border: none; width: 100px;"></td>
                    </tr>
                </table>
            </div>

            <div class="payroll-note" style="margin-top: 10px; font-size: 12px; color: #666;">
                <strong>หมายเหตุ:</strong> จำนวนพนักงานทั้งหมด {{ count($period->payrolls) }} ท่าน
            </div>

            <div class="footer">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>ผู้จัดทำ</div>
                    <div class="signature-date" style="font-size: 12px; color: #999; margin-top: 5px;">วันที่:
                        .......................................</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>ผู้อนุมัติ</div>
                    <div class="signature-date" style="font-size: 12px; color: #999; margin-top: 5px;">วันที่:
                        .......................................</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
