<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>{{ isset($isEvaluation) && $isEvaluation ? 'ใบประเมิน' : 'รายงานผลประกอบการ' }} ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
    </title>
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
            /* ลดจาก 14pt */
            color: #000;
            line-height: 1;
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
            /* ลดจาก 16pt */
            font-weight: bold;
            margin-bottom: 10px;
            /* ลดจาก 20px */
            line-height: 1.1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 1px 5px;
            /* ลดจาก 2px เพื่อความกะทัดรัดสูงสุด */
            border: none;
            vertical-align: top;
        }

        .section-header td {
            text-align: center;
            font-weight: bold;
            border-top: 2px solid #96c93d;
            /* Green line */
            padding-top: 5px;
            /* ลดจาก 8px */
            padding-bottom: 2px;
            /* ลดจาก 3px */
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

        .sub-item {
            padding-left: 20px;
        }

        .profit-loss-table th,
        .profit-loss-table td {
            text-align: center;
        }

        .profit-loss-table th {
            border-top: 2px solid #96c93d;
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
            opacity: 0.2; /* ความจางของลายน้ำ */
            z-index: -100;
            pointer-events: none; /* เพื่อไม่ให้ภาพบังการคลิกหรือการใช้งานอื่นๆ */
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
        {{ isset($isEvaluation) && $isEvaluation ? 'ใบประเมิน' : 'รายงานผลประกอบการ' }} {{ $displayTitle }} ปี {{ $year + 543 }}<br>
        @if(!empty($branchTitles))
            <b style="font-size: 10pt;">สาขา: {{ implode(', ', $branchTitles) }}</b><br>
        @endif
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }}
    </div>

    <table>
        <colgroup>
            <col style="width: 45%;">
            <col style="width: 25%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
        </colgroup>

        @if (empty($isEvaluation))
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

        @foreach ($data['details']['api_revenue_list'] as $item)
            <tr>
                <td colspan="2">ค่าคอร์ส (Math) {{ $item->branch_name }}</td>
                <td class="text-right">{{ number_format($item->revenue, 2) }}</td>
                <td></td>
            </tr>
        @endforeach

        @foreach ($data['details']['rv_items'] as $voucher)
            @foreach ($voucher->receiptItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endforeach

        <tr class="summary-row">
            <td colspan="2">รวมรายรับ</td>
            <td class="text-right">{{ number_format($data['total_revenue'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>
        @endif



        <!-- รายจ่าย -->
        <tr class="section-header">
            <td colspan="4">รายจ่าย</td>
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
        @if (empty($isEvaluation))
        <tr>
            <td colspan="2">รายการหักประจำเดือน (ใบสำคัญจ่าย + Payroll)</td>
            <td class="text-right">{{ number_format($data['pv_deductions'] + $data['payroll_deductions'], 2) }}</td>
            <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
        </tr>

        
        <!-- ผลประกอบการ -->
        <tr class="section-header">
            <td colspan="3" style="padding-top: 15px;">ผลประกอบการ</td>
        </tr>
        @endif
    </table>

    @if (empty($isEvaluation))

    <table class="profit-loss-table">
        <tr>
            <th style="width: 33%;">รายรับ</th>
            <th style="width: 33%;">รายจ่าย</th>
            <th style="width: 33%;">กำไร/ขาดทุน</th>
        </tr>
        <tr class="font-bold">
            <td style="border-bottom: 2px solid #96c93d; padding-bottom: 5px;">
                {{ number_format($data['total_revenue'], 2) }}</td>
            <td style="border-bottom: 2px solid #96c93d; padding-bottom: 5px;">
                {{ number_format($data['total_expense'], 2) }}</td>
            <td style="border-bottom: 2px solid #96c93d; padding-bottom: 5px;">{{ number_format($data['profit'], 2) }}
            </td>
        </tr>
    </table>
    @endif
</body>

</html>
