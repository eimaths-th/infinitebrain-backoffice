<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบจ่ายเงินเดือน - {{ $payroll->employee_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            padding: 20px;
        }

        .payslip-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
        }

        .company-address {
            font-size: 12px;
            color: #555;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .employee-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .info-group {
            display: flex;
            gap: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .content-row {
            display: flex;
            gap: 20px;
        }

        .content-col {
            flex: 1;
        }

        .section-title {
            font-weight: bold;
            padding: 8px;
            background: #e9ecef;
            margin-bottom: 10px;
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
            margin-bottom: 15px;
        }

        table td {
            padding: 5px 8px;
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
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .net-pay-label {
            font-size: 16px;
        }

        .net-pay-amount {
            font-size: 24px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }

        .payment-info {
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .payslip-container {
                border: 1px solid #333;
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
