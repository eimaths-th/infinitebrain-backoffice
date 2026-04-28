<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>{{ isset($isEvaluation) && $isEvaluation ? 'ใบประเมินรายปี' : 'รายงานผลประกอบการ ประจำปี' }} {{ $year + 543 }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 25mm;
        }

        body {
            font-family: 'Sarabun', 'Cordia New', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 16pt;
            color: #000;
            line-height: 1;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .header {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            padding: 1px 5px;
            border: none;
            vertical-align: top;
        }

        .section-header td {
            text-align: center;
            font-weight: bold;
            border-top: 2px solid #96c93d;
            padding-top: 5px;
            padding-bottom: 2px;
        }

        .col-headings th {
            font-weight: normal;
        }

        .summary-row td {
            font-weight: bold;
            border-top: 2px solid #96c93d;
            border-bottom: 2px solid #96c93d;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .sub-item { padding-left: 20px; }

        .profit-loss-table th,
        .profit-loss-table td {
            text-align: center;
        }

        .profit-loss-table th {
            border-top: 2px solid #96c93d;
            border-bottom: 1px solid #000;
            padding-top: 5px;
        }

        @if (isset($selectedGroups) && (in_array('EL', $selectedGroups) || in_array('IF', $selectedGroups)))
            .watermark-bg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: auto;
            max-height: 70%;
            opacity: 0.2;
            z-index: -100;
            pointer-events: none;
            object-fit: contain;
        }
        @endif
    </style>
</head>

<body onload="window.print()">
    @if(isset($selectedGroups) && (in_array('EL', $selectedGroups) || in_array('IF', $selectedGroups)))
        <img src="{{ asset('images/infinite.jpg') }}" class="watermark-bg" alt="watermark">
    @endif

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
        {{ isset($isEvaluation) && $isEvaluation ? 'ใบประเมินรายปี' : 'รายงานผลประกอบการรายปี' }} {{ $displayTitle }}<br>
        @if(!empty($branchTitles))
            <small style="font-size: 10pt;">สาขา: {{ implode(', ', $branchTitles) }}</small><br>
        @endif
        ประจำปี {{ $year + 543 }}
    </div>

    <table>
        <colgroup>
            <col style="width: 45%;">
            <col style="width: 25%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
        </colgroup>
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

        @foreach ($data['details']['api_revenue_list'] as $item)
            <tr>
                <td colspan="2">ค่าคอร์ส (Math) {{ $item->branch_name }}</td>
                <td class="text-right">{{ number_format($item->revenue, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <!-- สำหรับรายปี เราสรุป Receipt Vouchers ตามคำอธิบายแทนที่จะแยกตามใบ -->
        @php
            $rv_grouped = $data['details']['rv_items']->flatMap->receiptItems->groupBy('description')->map->sum('amount');
        @endphp
        @foreach ($rv_grouped as $desc => $amount)
            <tr>
                <td>{{ $desc }}</td>
                <td></td>
                <td class="text-right">{{ number_format($amount, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr class="summary-row">
            <td colspan="2">รวมรายรับ</td>
            <td class="text-right">{{ number_format($data['total_revenue'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>

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
                <td colspan="2">{{ \App\Models\PaymentVoucher::getAllCategoryLabels()[$category] ?? 'อื่นๆ' }}</td>
                <td class="text-right">{{ number_format($categoryTotal, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr class="summary-row">
            <td colspan="2">รวมรายจ่าย</td>
            <td class="text-right">{{ number_format($data['total_expense'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>

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
