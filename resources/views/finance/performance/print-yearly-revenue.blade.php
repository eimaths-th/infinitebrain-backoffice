<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานรายละเอียดรายรับรายปี ปี {{ $year + 543 }}</title>
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

        .no-break {
            break-inside: avoid;
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
        รายงานรายละเอียดรายรับรายปี {{ $displayTitle }}<br>
        @if(!empty($branchTitles))
            <small style="font-size: 10pt;">สาขา: {{ implode(', ', $branchTitles) }}</small><br>
        @endif
        ประจำปี {{ $year + 543 }}
    </div>

    @php $is_el = in_array('EL', $selectedGroups ?? []); @endphp

    <!-- 1. School Receipts Summary -->
    @if($is_el && count($data['details']['school_groups']) > 0)
        <div class="section-title">1. สรุปรายรับจากการลงทะเบียน (School Receipts)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 40%;">กลุ่มวิชา</th>
                    <th style="width: 15%;">จำนวนใบเสร็จ</th>
                    <th style="width: 15%;">จำนวนนักเรียน</th>
                    <th style="width: 20%;">ยอดรวม</th>
                </tr>
            </thead>
            <tbody>
                @php $idx = 1; $total = 0; @endphp
                @foreach($data['details']['school_groups'] as $group)
                    <tr>
                        <td class="text-center">{{ $idx++ }}</td>
                        <td>{{ $group->title }}</td>
                        <td class="text-center">{{ number_format($group->receipt_count) }}</td>
                        <td class="text-center">{{ number_format($group->student_count) }}</td>
                        <td class="text-right">{{ number_format($group->total_amount, 2) }}</td>
                    </tr>
                    @php $total += $group->total_amount; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="4" class="text-right">รวม School Receipts</td>
                    <td class="text-right">{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- 2. Eimaths Math Course (API) -->
    @if(!empty($data['details']['api_revenue_list']))
        <div class="section-title">2. รายรับจากระบบ Eimaths API (Math Course)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 30%;">สาขา</th>
                    <th style="width: 20%;">รหัสสาขา</th>
                    <th style="width: 20%;">แหล่งที่มา</th>
                    <th style="width: 20%;">ยอดรวม</th>
                </tr>
            </thead>
            <tbody>
                @php $idx = 1; $api_total = 0; @endphp
                @foreach($data['details']['api_revenue_list'] as $item)
                    <tr>
                        <td class="text-center">{{ $idx++ }}</td>
                        <td>{{ $item->branch_name }}</td>
                        <td class="text-center">{{ $item->branch_code }}</td>
                        <td class="text-center">{{ $item->source }}</td>
                        <td class="text-right">{{ number_format($item->revenue, 2) }}</td>
                    </tr>
                    @php $api_total += $item->revenue; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="4" class="text-right">รวม Math Course (API)</td>
                    <td class="text-right">{{ number_format($api_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- 3. Receipt Vouchers (สรุปตามรายการ) -->
    @php
        $rv_items = $data['details']['rv_items']->flatMap->receiptItems->groupBy('description')->map(function($items) {
            return (object)[
                'description' => $items->first()->description,
                'total_amount' => $items->sum('amount'),
                'count' => $items->count()
            ];
        })->sortByDesc('total_amount')->values();
    @endphp

    @if($rv_items->count() > 0)
        <div class="section-title">3. รายรับอื่นๆ (Receipt Vouchers Summary)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 60%;">รายละเอียด</th>
                    <th style="width: 10%;">จำนวนรายการ</th>
                    <th style="width: 20%;">ยอดรวม</th>
                </tr>
            </thead>
            <tbody>
                @php $idx = 1; $rv_total = 0; @endphp
                @foreach($rv_items as $item)
                    <tr>
                        <td class="text-center">{{ $idx++ }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ number_format($item->count) }}</td>
                        <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @php $rv_total += $item->total_amount; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="3" class="text-right">รวม Receipt Vouchers</td>
                    <td class="text-right">{{ number_format($rv_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Grand Total -->
    <table>
        <tr class="summary-row" style="border: 2px solid #5B9541; font-size: 12pt;">
            <td style="width: 70%;" class="text-right font-bold">รวมรายรับทั้งหมด (Total Revenue Yearly)</td>
            <td style="width: 30%;" class="text-right font-bold">{{ number_format($data['total_revenue'], 2) }} บาท</td>
        </tr>
    </table>

    <div class="footer">
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
