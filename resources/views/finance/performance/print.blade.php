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
            font-size: 16pt;
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
        รายงานผลประกอบการ อายเลเวล ปี {{ $year + 543 }}<br>
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }}
    </div>

    <table>
        <colgroup>
            <col style="width: 70%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
        </colgroup>
        <!-- รายรับ -->
        <tr class="section-header">
            <td colspan="3">รายรับ</td>
        </tr>
        <tr class="col-headings">
            <th>รายการ</th>
            <th class="text-right" colspan="2">จำนวนเงิน</th>
        </tr>

        @foreach ($data['details']['school_groups'] as $item)
            <tr>
                <td>ค่าคอร์ส {{ $item->group }}</td>
                <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        @foreach ($data['details']['rv_items'] as $item)
            <tr>
                <td>
                    {{ $item->remark ?: 'รายรับอื่นๆ' }}
                    @php $from = $item->payee ? $item->payee->name : $item->received_from; @endphp
                    @if($from)
                        ({{ $from }})
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->net_amount, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr class="summary-row">
            <td>รวมรายรับ</td>
            <td class="text-right">{{ number_format($data['total_revenue'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>

        <!-- รายจ่าย -->
        <tr class="section-header">
            <td colspan="3" style="border-top: none;">รายจ่าย</td>
        </tr>
        <tr class="col-headings">
            <th>รายการ</th>
            <th class="text-right" colspan="2">จำนวนเงิน</th>
        </tr>

        @if (count($data['details']['payroll_groups']) > 0)
            <tr>
                <td class="font-bold">เงินเดือนพนักงาน</td>
                <td></td>
                <td></td>
            </tr>
            @foreach ($data['details']['payroll_groups'] as $item)
                <tr>
                    <td class="sub-item">พนักงาน {{ $item->department }}</td>
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endif

        @foreach ($data['details']['pv_groups'] as $item)
            <tr>
                <td>{{ $item->pay_to }}</td>
                <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr class="summary-row">
            <td>รวมรายจ่าย</td>
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
