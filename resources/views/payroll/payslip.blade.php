<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบจ่ายเงินเดือน - {{ $payroll->employee_name }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        body {
            font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            padding: 15px;
        }

        .payslip-container {
            max-width: 100%;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
        }

        .company-address {
            font-size: 10px;
            color: #555;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 6px;
        }

        .employee-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 6px;
            background: #f5f5f5;
            border-radius: 3px;
        }

        .info-group {
            display: flex;
            gap: 6px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .content-row {
            display: flex;
            gap: 12px;
        }

        .content-col {
            flex: 1;
        }

        .section-title {
            font-weight: bold;
            padding: 5px;
            background: #e9ecef;
            margin-bottom: 6px;
            border-radius: 3px;
        }

        .income-section .section-title {
            background: #d4edda;
            color: #155724;
        }

        .deduction-section .section-title {
            background: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table td {
            padding: 3px 6px;
            border-bottom: 1px solid #ddd;
        }

        table td:last-child {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            border-top: 2px solid #333;
        }

        .net-pay-section {
            margin-top: 10px;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .net-pay-label {
            font-size: 13px;
        }

        .net-pay-amount {
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 150px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 5px;
        }

        .payment-info {
            margin-top: 8px;
            padding: 6px;
            background: #fff3cd;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 10mm;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .payslip-container {
                border: 1px solid #333;
                max-width: 100%;
            }

            * {
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .employee-info {
                background: #f5f5f5 !important;
            }

            .income-section .section-title {
                background: #d4edda !important;
                color: #155724 !important;
            }

            .deduction-section .section-title {
                background: #f8d7da !important;
                color: #721c24 !important;
            }

            .net-pay-section {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
            }

            .payment-info {
                background: #fff3cd !important;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; cursor: pointer;">
            🖨️ พิมพ์ใบเงินเดือน
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            ✕ ปิด
        </button>
    </div>

    <div class="payslip-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">บริษัท อินฟินิท เบรน จำกัด (สำนักงานใหญ่)</div>
            <div class="company-address">555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</div>
            <div class="company-address">เลขประจำตัวผู้เสียภาษี: 0125563031038</div>
            <div class="title">ใบจ่ายเงินเดือน/ค่าจ้าง/เงินประจำตำแหน่ง</div>
        </div>

        <!-- Employee Info -->
        <div class="employee-info">
            <div class="info-group">
                <span class="info-label">รหัส:</span>
                <span>{{ $payroll->employee_code }}</span>
            </div>
            <div class="info-group">
                <span class="info-label">ชื่อ-นามสกุล:</span>
                <span>{{ $payroll->employee_name }}</span>
            </div>
            <div class="info-group">
                <span class="info-label">ตำแหน่ง:</span>
                <span>{{ $payroll->position }}</span>
            </div>
        </div>

        <div class="payment-info">
            <div>
                <span class="info-label">บัญชีธนาคาร:</span>
                <span>{{ $payroll->bank_name }} {{ $payroll->bank_account }}</span>
            </div>
            <div>
                <span class="info-label">งวด:</span>
                <span>{{ $period->period_name }}</span>
            </div>
        </div>

        <!-- Content -->
        <div class="content-row" style="margin-top: 20px;">
            @php
                $incomeCount = $payroll->incomeItems->count();
                $deductionCount = $payroll->deductionItems->count();
                $maxRows = 1 + max($incomeCount, $deductionCount);
            @endphp
            <!-- Income -->
            <div class="content-col income-section">
                <div class="section-title">รายได้</div>
                <table>
                    @foreach ($payroll->incomeItems as $item)
                        <tr>
                            <td>{{ $item->description }}@if ($item->quantity > 1)
                                    (×{{ $item->quantity }})
                                @endif
                            </td>
                            <td>{{ number_format($item->quantity * $item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    @for ($i = $incomeCount; $i < $maxRows; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                    <tr class="total-row">
                        <td>รวมเงินได้</td>
                        <td>{{ number_format($payroll->total_income, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- Deduction -->
            <div class="content-col deduction-section">
                <div class="section-title">รายการหัก</div>
                <table>
                    @foreach ($payroll->deductionItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    @for ($i = $deductionCount; $i < $maxRows; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                    <tr class="total-row">
                        <td>รวมรายการหัก</td>
                        <td>{{ number_format($payroll->total_deduction, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="net-pay-section">
            <div class="net-pay-label">
                <div>เงินรับสุทธิ</div>
                <div style="font-size: 12px; opacity: 0.8;">วันที่จ่าย: {{ $period->payment_date?->format('d/m/Y') }}
                </div>
            </div>
            <div class="net-pay-amount">{{ number_format($payroll->net_pay, 2) }} บาท</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signature-box">
                <div class="signature-line">ผู้รับเงิน</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">ผู้จ่ายเงิน</div>
            </div>
        </div>
    </div>
</body>

</html>
