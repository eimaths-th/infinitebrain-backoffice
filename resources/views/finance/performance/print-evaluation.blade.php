<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบประเมิน ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
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
            font-size: 14pt;
            color: #000;
            line-height: 1.2;
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
            margin-bottom: 15px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 3px 5px;
            border: none;
            vertical-align: top;
        }

        .section-header td {
            text-align: center;
            font-weight: bold;
            border-top: 2px solid #96c93d;
            padding-top: 8px;
            padding-bottom: 3px;
        }

        .col-headings th {
            font-weight: normal;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
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

        .category-row td {
            font-weight: bold;
            background: #f5f5f5;
        }

        .detail-row td {
            font-size: 12pt;
        }

        .watermark-bg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: auto;
            max-height: 70%;
            opacity: 0.15;
            z-index: -100;
            pointer-events: none;
            object-fit: contain;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 14pt;
        }
    </style>
</head>

<body onload="window.print()">
    {{-- ลายน้ำ --}}
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
        ใบประเมิน {{ $displayTitle }} ปี {{ $year + 543 }}<br>
        @if(!empty($branchTitles))
            <small style="font-size: 10pt;">สาขา: {{ implode(', ', $branchTitles) }}</small><br>
        @endif
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }}
    </div>

    @if($evaluation_items->isEmpty())
        <div class="no-data">
            <p>ไม่มีรายการใบสำคัญจ่ายที่เข้าใบประเมินในเดือนนี้</p>
        </div>
    @else
        {{-- สรุปตามหมวดหมู่ --}}

        
        <table>
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 42%;">
                <col style="width: 15%;">
                <col style="width: 20%;">
                <col style="width: 15%;">
            </colgroup>
            <tr class="section-header">
                <td colspan="5">สรุปรายจ่ายที่เข้าใบประเมิน</td>
            </tr>
            <tr class="col-headings">
                <th class="text-center">ลำดับ</th>
                <th>หมวดหมู่</th>
                <th class="text-center">จำนวน (รายการ)</th>
                <th class="text-right">ยอดรวม</th>
                <th></th>
            </tr>

            @foreach ($category_totals as $index => $cat)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $cat->label }}</td>
                    <td class="text-center">{{ $cat->count }}</td>
                    <td class="text-right">{{ number_format($cat->total, 2) }}</td>
                    <td></td>
                </tr>
            @endforeach

            <tr class="summary-row">
                <td colspan="3">รวมทั้งสิ้น ({{ $evaluation_items->count() }} รายการ)</td>
                <td class="text-right">{{ number_format($total_evaluation, 2) }}</td>
                <td style="font-weight: normal; text-align: left; padding-left: 10px;">บาท</td>
            </tr>
        </table>

        {{-- รายละเอียดแต่ละรายการ --}}
        <table style="margin-top: 20px;">
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 12%;">
                <col style="width: 15%;">
                <col style="width: 30%;">
                <col style="width: 15%;">
                <col style="width: 20%;">
            </colgroup>
            <tr class="section-header">
                <td colspan="6" style="border-top: none;">รายละเอียดใบสำคัญจ่ายที่เข้าใบประเมิน</td>
            </tr>
            <tr class="col-headings">
                <th class="text-center">ลำดับ</th>
                <th class="text-center">วันที่</th>
                <th>เลขที่ PV</th>
                <th>จ่ายให้ / รายละเอียด</th>
                <th>หมวดหมู่</th>
                <th class="text-right">ยอดสุทธิ</th>
            </tr>

            @foreach ($evaluation_items as $index => $voucher)
                <tr class="detail-row">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                    <td>{{ $voucher->voucher_no }}</td>
                    <td>
                        {{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }}
                        @if($voucher->expenseItems->count() > 0)
                            <br><small style="color: #666;">{{ $voucher->expenseItems->first()->description }}</small>
                        @endif
                    </td>
                    <td style="font-size: 11pt;">{{ \App\Models\PaymentVoucher::getAllCategoryLabels()[$voucher->category] ?? 'อื่นๆ' }}</td>
                    <td class="text-right">{{ number_format($voucher->net_amount, 2) }}</td>
                </tr>
            @endforeach

            <tr class="summary-row">
                <td colspan="5">รวมยอดใบประเมิน</td>
                <td class="text-right">{{ number_format($total_evaluation, 2) }}</td>
            </tr>
        </table>
    @endif
</body>

</html>
