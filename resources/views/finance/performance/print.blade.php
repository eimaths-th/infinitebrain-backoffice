<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานผลประกอบการ ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
    </title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: 'Sarabun', 'Cordia New', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .header {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 2px 5px;
            border: none;
            vertical-align: top;
        }

        .section-header td {
            text-align: center;
            font-weight: bold;
            border-top: 2px solid #5B9541;
            /* Green line */
            padding-top: 5px;
            padding-bottom: 2px;
        }

        .col-headings th {
            font-weight: normal;
        }

        .summary-row td {
            font-weight: bold;
            border-top: 2px solid #5B9541;
            border-bottom: 2px solid #5B9541;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .sub-item {
            padding-left: 20px;
        }

        .profit-loss-table th,
        .profit-loss-table td {
            text-align: center;
        }

        .profit-loss-table th {
            border-top: 2px solid #5B9541;
            border-bottom: 1px solid #000;
            padding-top: 5px;
        }
    </style>
</head>

<body onload="window.print()">
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
        รายงานผลประกอบการ {{ $displayTitle }} ปี {{ $year + 543 }}<br>
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }}
    </div>

    <table>
        <colgroup>
            <col style="width: 45%;">
            <col style="width: 25%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
        </colgroup>
        <!-- รายรับ -->
        <tr class="section-header">
            <td colspan="4">รายรับ</td>
        </tr>
        <tr class="col-headings">
            <th colspan="2">รายการ</th>
            <th class="text-right" colspan="2">จำนวนเงิน</th>
        </tr>

        @foreach ($data['details']['school_groups'] as $item)
            <tr>
                <td colspan="2">ค่าคอร์ส {{ $item->group }}</td>
                <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        @foreach ($data['details']['rv_items'] as $item)
            <tr>
                <td>{{ $item->remark ?: 'รายรับอื่นๆ' }}</td>
                <td>
                    @php $from = $item->payee ? $item->payee->name : $item->received_from; @endphp
                    {{ $from ? '('.$from.')' : '' }}
                </td>
                <td class="text-right">{{ number_format($item->net_amount, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr class="summary-row">
            <td colspan="2">รวมรายรับ</td>
            <td class="text-right">{{ number_format($data['total_revenue'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>

        <!-- รายจ่าย -->
        <tr class="section-header">
            <td colspan="4" style="border-top: none;">รายจ่าย</td>
        </tr>
        <tr class="col-headings">
            <th colspan="2">รายการ</th>
            <th class="text-right" colspan="2">จำนวนเงิน</th>
        </tr>

        @if (count($data['details']['payroll_groups']) > 0)
            <tr>
                <td class="font-bold" colspan="4">เงินเดือนพนักงาน</td>
            </tr>
            @foreach ($data['details']['payroll_groups'] as $item)
                <tr>
                    <td class="sub-item" colspan="2">พนักงาน {{ $item->department }}</td>
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endif

        @foreach ($data['details']['pv_items']->groupBy('category') as $category => $vouchers)
            @php
                $categoryTotal = $vouchers->sum('net_amount');
            @endphp
            <tr>
                <td colspan="2">หมวดหมู่: {{ \App\Models\PaymentVoucher::$categoryLabels[$category] ?? 'อื่นๆ' }}</td>
                <td class="text-right">{{ number_format($categoryTotal, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr class="summary-row">
            <td colspan="2">รวมรายจ่าย</td>
            <td class="text-right">{{ number_format($data['total_expense'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>

        <!-- ผลประกอบการ -->
        <tr class="section-header">
            <td colspan="3" style="border-top: none; padding-top: 15px;">ผลประกอบการ</td>
        </tr>
    </table>

    <table class="profit-loss-table">
        <tr>
            <th style="width: 33%;">รายรับ</th>
            <th style="width: 33%;">รายจ่าย</th>
            <th style="width: 33%;">กำไร/ขาดทุน</th>
        </tr>
        <tr class="font-bold">
            <td style="border-bottom: 2px solid #5B9541; padding-bottom: 5px;">
                {{ number_format($data['total_revenue'], 2) }}</td>
            <td style="border-bottom: 2px solid #5B9541; padding-bottom: 5px;">
                {{ number_format($data['total_expense'], 2) }}</td>
            <td style="border-bottom: 2px solid #5B9541; padding-bottom: 5px;">{{ number_format($data['profit'], 2) }}
            </td>
        </tr>
    </table>
</body>

</html>
