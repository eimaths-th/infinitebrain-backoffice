<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานรายละเอียดรายรับ {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}</title>
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

        /* ตาราง */
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
            background-color: #5B9541;
            color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }

        /* แถวสลับสี */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* แถวสรุปรวม */
        .summary-row {
            font-weight: bold;
            background-color: #e8f5e3 !important;
            border-top: 2px solid #5B9541;
        }

        /* หัวข้อกลุ่มวิชา - ป้องกันไม่ให้หัวข้อถูกแยกจากตารางข้อมูล */
        .group-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 3px;
            padding: 3px 8px;
            background-color: #f0f0f0;
            border-left: 4px solid #5B9541;
            break-after: avoid; /* ห้ามขึ้นหน้าใหม่หลังหัวข้อ */
            page-break-after: avoid; /* รองรับ browser เก่า */
        }

        /* หัวข้อส่วนหลัก */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            padding: 4px 8px;
            background-color: #e8f5e3;
            border-left: 4px solid #5B9541;
        }

        /* ตัวอักษรเล็ก */
        .small-text {
            font-size: 9pt;
            color: #666;
        }

        /* ป้องกันการตัดหน้า */
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
        รายงานรายละเอียดรายรับ {{ $displayTitle }}<br>
        ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
    </div>

    @php $is_el = in_array('EL', $selectedGroups ?? []); @endphp

    <!-- ส่วนที่ 1: School Receipts แบ่งตามกลุ่มวิชา (เฉพาะสาขา EL) -->
    @if($is_el && $school_receipts_detail->count() > 0)
        <div class="section-title">1. School Receipts (ค่าคอร์สจากระบบลงทะเบียน)</div>

        @php $grand_school_total = 0; @endphp
        @foreach($school_receipts_detail as $group)
            <!-- หัวข้อกลุ่มวิชา -->
            <div class="no-break">
            <div class="group-title">
                กลุ่มวิชา: {{ $group->group_name }} ({{ $group->group_code }})
                <span style="float: right;">รวม: {{ number_format($group->total, 2) }} บาท</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 10%;">วันที่</th>
                        <th style="width: 12%;">เลขที่ใบเสร็จ</th>
                        <th style="width: 22%;">ชื่อนักเรียน</th>
                        {{-- <th style="width: 15%;">วิชา</th>
                        <th style="width: 7%;">ระดับ</th>
                        <th style="width: 6%;">สัปดาห์</th>
                        <th style="width: 6%;">จำนวน</th> --}}
                        <th style="width: 18%;">ยอดรวม</th>
                    </tr>
                </thead>
                <tbody>
                    @php $idx = 1; @endphp
                    @foreach($group->items as $item)
                        <tr>
                            <td class="text-center">{{ $idx++ }}</td>
                            <td>{{ $item->rec_school ? \Carbon\Carbon::parse($item->rec_school->submission_date)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->rec_school->receipt_number ?? '-' }}</td>
                            <td>
                                @if($item->std)
                                    {{ $item->std->first_name ?? '' }} {{ $item->std->last_name ?? '' }}
                                @else
                                    {{ $item->student_id }}
                                @endif
                            </td>
                            {{-- <td>{{ $item->school_sub->subject ?? $item->subject }}</td>
                            <td class="text-center">{{ $item->level }}</td>
                            <td class="text-center">{{ $item->week }}</td>
                            <td class="text-center">{{ $item->qty }}</td> --}}
                            <td class="text-right">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="4" class="text-right">รวมกลุ่ม {{ $group->group_name }}</td>
                        <td class="text-right">{{ number_format($group->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            </div>
            @php $grand_school_total += $group->total; @endphp
        @endforeach

        <!-- สรุปรวม School Receipts -->
        <table>
            <tr class="summary-row" style="border: 2px solid #5B9541;">
                <td style="width: 75%;" class="text-right font-bold">รวม School Receipts ทั้งหมด</td>
                <td style="width: 25%;" class="text-right font-bold">{{ number_format($grand_school_total, 2) }} บาท</td>
            </tr>
        </table>
    @endif

    <!-- ส่วน Receipt Vouchers (ใบสำคัญรับ) -->
    <div class="no-break">
    <div class="section-title">{{ $is_el ? '2' : '1' }}. Receipt Vouchers (ใบสำคัญรับอื่นๆ)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">วันที่</th>
                <th style="width: 13%;">เลขที่ใบสำคัญ</th>
                <th style="width: 20%;">รับจาก</th>
                <th style="width: 25%;">รายละเอียด</th>
                <th style="width: 25%;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @php $rv_total = 0; $idx = 1; @endphp
            @forelse($data['details']['rv_items'] as $voucher)
                @foreach($voucher->receiptItems as $item)
                    <tr>
                        <td class="text-center">{{ $idx++ }}</td>
                        <td>{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                        <td>{{ $voucher->voucher_no }}</td>
                        <td>
                            @php $from = $voucher->payee ? $voucher->payee->name : $voucher->received_from; @endphp
                            {{ $from ?: '-' }}
                        </td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @php $rv_total += $item->amount; @endphp
                @endforeach
            @empty
                <tr><td colspan="6" class="text-center">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="5" class="text-right">รวม Receipt Vouchers</td>
                <td class="text-right">{{ number_format($rv_total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    </div>

    <!-- สรุปรวมรายรับทั้งหมด -->
    <table>
        <tr class="summary-row" style="border: 2px solid #5B9541; font-size: 12pt;">
            <td style="width: 70%;" class="text-right font-bold">รวมรายรับทั้งหมด (Total Revenue)</td>
            <td style="width: 30%;" class="text-right font-bold">{{ number_format($data['total_revenue'], 2) }} บาท</td>
        </tr>
    </table>

    <!-- ส่วนท้ายรายงาน -->
    <div class="footer">
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
