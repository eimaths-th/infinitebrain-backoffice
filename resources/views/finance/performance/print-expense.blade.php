<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานรายละเอียดรายจ่าย{{ isset($isEvaluation) && $isEvaluation ? ' (แบบประเมิน)' : '' }} {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}</title>
    <style>
        /* การตั้งค่าหน้ากระดาษ A4 */
        @page {
            size: A4 portrait;
            margin: 25mm;
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

        th,
        td {
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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-end {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

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

        .section-title.deduction {
            background-color: #fff3e0;
            border-left: 4px solid #f39c12;
        }

        table.table-warning th {
            background-color: #f39c12 !important;
        }

        table.table-warning .summary-row {
            background-color: #fff3e0 !important;
            border-top: 2px solid #f39c12;
        }

        /* หัวข้อกลุ่มวิชา / หมวดหมู่คล้าย print-revenue */
        .group-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            background-color: {{ isset($isEvaluation) && $isEvaluation ? '#e8f4fd' : '#fde8e8' }};
            padding: 5px 10px;
            border-left: 4px solid {{ isset($isEvaluation) && $isEvaluation ? '#3498db' : '#e74c3c' }};
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
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <!-- ส่วนหัวรายงาน -->
    <div class="header">
        @php
            $titles = [];
            foreach ($selectedGroups ?? [] as $g) {
                $titles[] = $groupMap[$g] ?? $g;
            }
            $displayTitle = implode(' & ', $titles);

            $branchTitles = [];
            if (!empty($selectedBranches)) {
                $branchTitles = \App\Models\Branch::whereIn('code', $selectedBranches)->pluck('name')->toArray();
            }
        @endphp
        รายงาน{{ isset($isEvaluation) && $isEvaluation ? 'ประเมิน' : 'รายละเอียด' }}รายจ่าย {{ $displayTitle }}<br>
        @if(!empty($branchTitles))
            <small style="font-size: 10pt;">สาขา: {{ implode(', ', $branchTitles) }}</small><br>
        @endif
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
    </div>

    <!-- ส่วนที่ 1: Payment Vouchers (ใบสำคัญจ่าย) -->
    <div class="section-title">1. Payment Vouchers (ใบสำคัญจ่าย)</div>
    @php
        $groupedPV = collect($data['details']['pv_items'])->groupBy('category');
        $pv_grand_total = 0;
    @endphp

    @forelse($groupedPV as $category => $vouchers)
        @php
            $categoryName = \App\Models\PaymentVoucher::getAllCategoryLabels()[$category] ?? 'อื่นๆ';
            // คำนวณยอดรวมของหมวดหมู่ก่อนเพื่อแสดงบนแถบ title
            $category_total = $vouchers->sum(function ($voucher) {
                return $voucher->expenseItems->sum('amount');
            });
            $idx = 1;
        @endphp

        <div class="no-break">
            <div class="group-title">
                หมวดหมู่: {{ $categoryName }}
                <span style="float: right;">รวม: {{ number_format($category_total, 2) }} บาท</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">วันที่</th>
                        <th style="width: 15%;">เลขที่</th>
                        <th style="width: 45%;">รายละเอียด / จ่ายให้</th>
                        <th style="width: 20%;">จำนวนเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vouchers as $voucher)
                        @foreach ($voucher->expenseItems as $item)
                            <tr>
                                <td class="text-center">{{ $idx++ }}</td>
                                <td class="text-center">{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $voucher->voucher_no }}</td>
                                <td>
                                    {{ $item->description }}
                                    <div class="small-text">
                                        {{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }}</div>
                                </td>
                                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="4" class="text-right">รวมหมวดหมู่ {{ $categoryName }}</td>
                        <td class="text-right">{{ number_format($category_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @php $pv_grand_total += $category_total; @endphp
    @empty
        <table style="margin-top: 10px;">
            <tr>
                <td class="text-center">ไม่มีข้อมูล Payment Vouchers</td>
            </tr>
        </table>
    @endforelse

    @if ($groupedPV->isNotEmpty())
        <table>
            <tr class="summary-row" style="border: 2px solid #e74c3c;">
                <td style="width: 70%;" class="text-right font-bold">ยอดรวม Payment Vouchers ทั้งหมด</td>
                <td style="width: 30%;" class="text-right font-bold">{{ number_format($pv_grand_total, 2) }}
                    บาท</td>
            </tr>
        </table>
    @endif

    <!-- ส่วนที่ 2: Payroll (เงินเดือน) - แสดงเฉพาะสาขา EL/IF ที่มี Payroll และไม่อยู่ในโหมดใบประเมิน -->
    @php $payroll_grand_total = 0; @endphp
    @if (empty($isEvaluation))
        @if ($data['details']['payroll_items']->count() > 0)
            <div class="no-break">
                <div class="section-title">2. Payroll (เงินเดือนพนักงาน)</div>
                @php
                    // จัดกลุ่มตามแผนก
                    $groupedPayroll = collect($data['details']['payroll_items'])->groupBy(function ($item) {
                        return $item->user->department ?? 'ไม่ระบุแผนก';
                    });
                @endphp

                @foreach ($groupedPayroll as $department => $items)
                    @php
                        $dept_total = $items->sum('net_pay');
                        $idx = 1;
                    @endphp

                    <div class="no-break">
                        <div class="group-title">
                            แผนก: {{ $department }}
                            <span style="float: right;">รวม: {{ number_format($dept_total, 2) }} บาท</span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 45%;">ชื่อ-นามสกุล</th>
                                    <th style="width: 20%;">งวด (Period)</th>
                                    <th style="width: 30%;">ยอดสุทธิ (Net Pay)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td class="text-center">{{ $idx++ }}</td>
                                        <td>{{ $item->user->name ?? $item->user_id }}</td>
                                        <td class="text-center">
                                            {{ $item->payrollPeriod->month }}/{{ $item->payrollPeriod->year }}</td>
                                        <td class="text-right">{{ number_format($item->net_pay, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="summary-row">
                                    <td colspan="3" class="text-right">รวมแผนก {{ $department }}</td>
                                    <td class="text-right">{{ number_format($dept_total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @php $payroll_grand_total += $dept_total; @endphp
                @endforeach
            </div>
        @endif
    @endif

    <!-- ส่วนที่ 3: รายการหัก (Deductions) -->
    @php
        $actual_deductions = $data['pv_deductions'] + (empty($isEvaluation) ? $data['payroll_deductions'] : 0);
    @endphp
    @if ($actual_deductions > 0)
        <div class="no-break">
            <div class="section-title deduction">3. รายการหักประจำเดือน (Deductions)</div>
            <table class="table-warning">
                <thead>
                    <tr>
                        <th style="width: 70%;">ประเภทรายการหัก</th>
                        <th style="width: 30%;">จำนวนเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($data['pv_deductions'] > 0)
                    <tr>
                        <td>รายการหัก และภาษีหัก ณ ที่จ่าย จากใบสำคัญจ่าย</td>
                        <td class="text-right">{{ number_format($data['pv_deductions'], 2) }}</td>
                    </tr>
                    @endif
                    @if (empty($isEvaluation) && $data['payroll_deductions'] > 0)
                    <tr>
                        <td>รายการหักจากเงินเดือน (ประกันสังคม, ภาษี, และอื่นๆ)</td>
                        <td class="text-right">{{ number_format($data['payroll_deductions'], 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td class="text-right">รวมรายการหักประจำเดือน</td>
                        <td class="text-right">{{ number_format($actual_deductions, 2) }} บาท</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- สรุปรวมรายจ่ายทั้งหมด -->
    <table>
        @if (empty($isEvaluation))
        <tr class="summary-row" style="border: 2px solid #e74c3c;">
            <td style="width: 70%;" class="text-right font-bold"> ยอดรวม Payroll ทั้งหมด </td>
            <td style="width: 30%;" class="text-right font-bold">{{ number_format($payroll_grand_total, 2) }}
                บาท</td>
        </tr>
        @endif
        <tr class="summary-row" style="border: 2px solid #e74c3c;">
            <td style="width: 70%;" class="text-right font-bold">รวมรายจ่ายทั้งหมด {{ isset($isEvaluation) && $isEvaluation ? '(ประเมิน)' : '' }}</td>
            <td style="width: 30%;" class="text-right font-bold">{{ number_format($pv_grand_total + $payroll_grand_total, 2) }} บาท
            </td>
        </tr>
    </table>

    <!-- ส่วนท้ายรายงาน -->
    <div class="footer">
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
