<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานรายละเอียดรายจ่าย {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}</title>
    <style>
        /* การตั้งค่าหน้ากระดาษ A4 */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: 'Sarabun', 'Cordia New', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            color: #000;
            line-height: 1.2;
        }

        /* ส่วนหัวรายงาน */
        .header {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .header .subtitle {
            font-size: 12pt;
            font-weight: normal;
            color: #555;
        }

        /* ตาราง */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 3px 6px;
            border: 1px solid #ccc;
            vertical-align: top;
        }

        th {
            background-color: #e74c3c;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        /* แถวสลับสี */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-end { text-align: right; }
        .font-bold { font-weight: bold; }

        /* แถวสรุปรวม */
        .summary-row {
            font-weight: bold;
            background-color: #fde8e8 !important;
            border-top: 2px solid #e74c3c;
        }

        /* หัวข้อส่วนย่อย */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            padding: 4px 8px;
            background-color: #f0f0f0;
            border-left: 4px solid #e74c3c;
        }

        /* ป้องกันการตัดหน้าในจุดที่ไม่เหมาะสม */
        .no-break {
            break-inside: avoid;
        }

        /* ส่วนท้ายรายงาน */
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 9pt;
            color: #888;
        }

        .small-text {
            font-size: 9pt;
            color: #666;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>

<body onload="window.print()">
    <!-- ส่วนหัวรายงาน -->
    <div class="header">
        @php
            $groupMap = [
                'EL' => 'Eye Level',
                'IF' => 'Infinite Brain',
                'META' => 'META Knowledge',
                'EiMaths' => 'eiMaths',
            ];
            $titles = [];
            foreach(($selectedGroups ?? []) as $g) {
                $titles[] = $groupMap[$g] ?? $g;
            }
            $displayTitle = implode(' & ', $titles);
        @endphp
        รายงานรายละเอียดรายจ่าย {{ $displayTitle }}<br>
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
    </div>

    <!-- ส่วนที่ 1: Payment Vouchers (ใบสำคัญจ่าย) -->
    <div class="section-title">1. Payment Vouchers (ใบสำคัญจ่าย)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 10%;">วันที่</th>
                <th style="width: 11%;">เลขที่</th>
                {{-- <th style="width: 8%;">สาขา</th> --}}
                <th style="width: 22%;">หมวดหมู่</th>
                <th style="width: 33%;">รายละเอียด / จ่ายให้</th>
                <th style="width: 20%;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @php $pv_total = 0; $idx = 1; @endphp
            @forelse($data['details']['pv_items'] as $voucher)
                @foreach($voucher->expenseItems as $item)
                    <tr>
                        <td class="text-center">{{ $idx++ }}</td>
                        <td>{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                        <td>{{ $voucher->voucher_no }}</td>
                        {{-- <td class="text-center">{{ $voucher->branch_code }}</td> --}}
                        <td>{{ \App\Models\PaymentVoucher::$categoryLabels[$voucher->category] ?? 'อื่นๆ' }}</td>
                        <td>
                            {{ $item->description }}
                            <div class="small-text">{{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }}</div>
                        </td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @php $pv_total += $item->amount; @endphp
                @endforeach
            @empty
                <tr><td colspan="6" class="text-center">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="5" class="text-right">รวม Payment Vouchers</td>
                <td class="text-right">{{ number_format($pv_total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- ส่วนที่ 2: Payroll (เงินเดือน) - แสดงเฉพาะสาขา EL/IF ที่มี Payroll -->
    @if($data['details']['payroll_items']->count() > 0)
        <div class="no-break">
            <div class="section-title">2. Payroll (เงินเดือนพนักงาน)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">ชื่อ-นามสกุล</th>
                        <th style="width: 20%;">แผนก (Department)</th>
                        <th style="width: 15%;">งวด (Period)</th>
                        <th style="width: 25%;">ยอดสุทธิ (Net Pay)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $payroll_total = 0; $idx = 1; @endphp
                    @foreach($data['details']['payroll_items'] as $item)
                        <tr>
                            <td class="text-center">{{ $idx++ }}</td>
                            <td>{{ $item->user->name ?? $item->user_id }}</td>
                            <td>{{ $item->user->department ?? '-' }}</td>
                            <td class="text-center">{{ $item->payrollPeriod->month }}/{{ $item->payrollPeriod->year }}</td>
                            <td class="text-right">{{ number_format($item->net_pay, 2) }}</td>
                        </tr>
                        @php $payroll_total += $item->net_pay; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="4" class="text-right">รวม Payroll</td>
                        <td class="text-right">{{ number_format($payroll_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- สรุปรวมรายจ่ายทั้งหมด -->
    <table>
        <tr class="summary-row" style="border: 2px solid #e74c3c;">
            <td style="width: 70%;" class="text-right font-bold">รวมรายจ่ายทั้งหมด (Total Expense)</td>
            <td style="width: 30%;" class="text-right font-bold">{{ number_format($data['total_expense'], 2) }} บาท</td>
        </tr>
    </table>

    <!-- ส่วนท้ายรายงาน -->
    <div class="footer">
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
