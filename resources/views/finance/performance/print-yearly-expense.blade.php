<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานรายละเอียดรายจ่ายรายปี ปี {{ $year + 543 }}</title>
    <style>
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

        .header {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            padding: 2px 5px;
            border: 1px solid #ccc;
            vertical-align: top;
        }

        th {
            background-color: #96c93d;
            color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .summary-row {
            font-weight: bold;
            background-color: #e8f5e3 !important;
            border-top: 2px solid #96c93d;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            padding: 4px 8px;
            background-color: #e8f5e3;
            border-left: 4px solid #96c93d;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 9pt;
            color: #888;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        @php
            $titles = [];
            foreach(($selectedGroups ?? []) as $g) {
                $titles[] = $groupMap[$g] ?? $g;
            }
            $displayTitle = implode(' & ', $titles);

            $branchTitles = [];
            if (!empty($selectedBranches)) {
                $branchTitles = \App\Models\Branch::whereIn('code', $selectedBranches)->pluck('name')->toArray();
            }
        @endphp
        รายงานรายละเอียดรายจ่ายรายปี {{ $displayTitle }}<br>
        @if(!empty($branchTitles))
            <small style="font-size: 10pt;">สาขา: {{ implode(', ', $branchTitles) }}</small><br>
        @endif
        ประจำปี {{ $year + 543 }}
    </div>

    <!-- 1. Payroll Summary -->
    @if(count($data['details']['payroll_groups']) > 0)
        <div class="section-title">1. รายจ่ายเงินเดือนพนักงาน (Payroll Summary)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 50%;">แผนก</th>
                    <th style="width: 20%;">จำนวนพนักงาน (สูงสุด)</th>
                    <th style="width: 20%;">ยอดรวม</th>
                </tr>
            </thead>
            <tbody>
                @php $idx = 1; $payroll_total = 0; @endphp
                @foreach($data['details']['payroll_groups'] as $group)
                    <tr>
                        <td class="text-center">{{ $idx++ }}</td>
                        <td>{{ $group->department }}</td>
                        <td class="text-center">{{ number_format($group->employee_count) }}</td>
                        <td class="text-right">{{ number_format($group->total_amount, 2) }}</td>
                    </tr>
                    @php $payroll_total += $group->total_amount; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="3" class="text-right">รวมเงินเดือนพนักงาน</td>
                    <td class="text-right">{{ number_format($payroll_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- 2. Payment Vouchers Summary by Category -->
    <div class="section-title">2. รายจ่ายอื่นๆ (Payment Vouchers Summary)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 50%;">หมวดหมู่</th>
                <th style="width: 20%;">จำนวนรายการ</th>
                <th style="width: 20%;">ยอดรวม</th>
            </tr>
        </thead>
        <tbody>
            @php $idx = 1; $pv_total = 0; @endphp
            @foreach($data['details']['pv_items']->groupBy('category') as $category => $vouchers)
                @php
                    $cat_total = $vouchers->sum('net_amount');
                    $label = \App\Models\PaymentVoucher::getAllCategoryLabels()[$category] ?? 'อื่นๆ';
                @endphp
                <tr>
                    <td class="text-center">{{ $idx++ }}</td>
                    <td>{{ $label }}</td>
                    <td class="text-center">{{ number_format($vouchers->count()) }}</td>
                    <td class="text-right">{{ number_format($cat_total, 2) }}</td>
                </tr>
                @php $pv_total += $cat_total; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="3" class="text-right">รวมรายจ่ายอื่นๆ</td>
                <td class="text-right">{{ number_format($pv_total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2.1 Top Payees (Top 20) -->
    <div class="section-title">2.1 สรุปยอดจ่ายตามผู้รับ (Top 20 Payees)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 70%;">จ่ายให้</th>
                <th style="width: 20%;">ยอดรวม</th>
            </tr>
        </thead>
        <tbody>
            @php $idx = 1; @endphp
            @foreach($data['details']['pv_groups']->take(20) as $group)
                <tr>
                    <td class="text-center">{{ $idx++ }}</td>
                    <td>{{ $group->pay_to }}</td>
                    <td class="text-right">{{ number_format($group->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Grand Total -->
    <table>
        <tr class="summary-row" style="border: 2px solid #5B9541; font-size: 12pt;">
            <td style="width: 70%;" class="text-right font-bold">รวมรายจ่ายทั้งหมด (Total Expense Yearly)</td>
            <td style="width: 30%;" class="text-right font-bold">{{ number_format($data['total_expense'], 2) }} บาท</td>
        </tr>
    </table>

    <div class="footer">
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
