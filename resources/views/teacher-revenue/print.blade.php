<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานรายได้ครูจากการสอน {{ $month ? \App\Models\PayrollPeriod::$thaiMonths[(int)$month] : 'ตลอดปี' }} {{ $year + 543 }}</title>
    <style>
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

        .header {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 5px 6px;
            border: 1px solid #ccc;
            vertical-align: top;
        }

        th {
            background-color: #3f6ad8;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .summary-row {
            font-weight: bold;
            background-color: #f0f3ff !important;
            border-top: 2px solid #3f6ad8;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
            padding: 4px 8px;
            background-color: #f0f0f0;
            border-left: 4px solid #3f6ad8;
        }

        .no-break { break-inside: avoid; }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 9pt;
            color: #888;
        }

        .small-text { font-size: 9pt; color: #666; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        รายงานสรุปรายได้ครูจากการสอน (Teacher Revenue)<br>
        @if($month)
            ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[(int)$month] }} ปี {{ $year + 543 }}
        @else
            ประจำปี {{ $year + 543 }}
        @endif
    </div>

    @php
        $total_all_revenue = collect($teacher_revenue)->sum('total_revenue');
        $total_all_hours = collect($teacher_revenue)->sum('total_hours');
        $total_all_sessions = collect($teacher_revenue)->sum('session_count');
    @endphp

    <div class="section-title">1. สรุปรายได้ครูแต่ละคน</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">ชื่อครู</th>
                <th style="width: 15%;">จำนวนครั้ง</th>
                <th style="width: 15%;">ชั่วโมงสอน</th>
                <th style="width: 15%;">เฉลี่ย/ชม.</th>
                <th style="width: 15%;">รายได้ (บาท)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teacher_revenue as $idx => $t)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $t['teacher_name'] }}</td>
                    <td class="text-center">{{ $t['session_count'] }}</td>
                    <td class="text-right">{{ number_format($t['total_hours'], 1) }}</td>
                    <td class="text-right">
                        @if($t['total_hours'] > 0)
                            {{ number_format($t['total_revenue'] / $t['total_hours'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-bold">{{ number_format($t['total_revenue'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">ไม่พบข้อมูลการสอนที่มีครู (user_id) ในช่วงเวลาที่เลือก</td></tr>
            @endforelse
        </tbody>
        @if(count($teacher_revenue) > 0)
        <tfoot>
            <tr class="summary-row">
                <td colspan="2" class="text-right">รวมทั้งหมด</td>
                <td class="text-center">{{ $total_all_sessions }}</td>
                <td class="text-right">{{ number_format($total_all_hours, 1) }} ชม.</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($total_all_revenue, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    @if(count($unmatched_records) > 0)
        <div class="no-break mt-4">
            <div class="section-title" style="border-left-color: #f39c12;">2. รายการที่ไม่พบข้อมูลใบเสร็จ (ไม่สามารถคำนวณรายได้ได้)</div>
            <table>
                <thead style="background-color: #f39c12; color: #fff;">
                    <tr>
                        <th style="width: 5%; background-color: #f39c12;">#</th>
                        <th style="width: 25%; background-color: #f39c12;">ครูผู้สอน</th>
                        <th style="width: 25%; background-color: #f39c12;">นักเรียน</th>
                        <th style="width: 20%; background-color: #f39c12;">วิชา</th>
                        <th style="width: 15%; background-color: #f39c12;">วันที่-เวลา</th>
                        <th style="width: 10%; background-color: #f39c12;">ชั่วโมง</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unmatched_records as $idx => $r)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $r->teacher_name }}</td>
                            <td>
                                {{ $r->student_name }}
                                @if($r->student_nickname)
                                    <div class="small-text">({{ $r->student_nickname }})</div>
                                @endif
                            </td>
                            <td>{{ $r->subject_name }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($r->datetime)->format('d/m/Y') }}<br>
                                <span class="small-text">{{ $r->stime }} - {{ $r->etime }}</span>
                            </td>
                            <td class="text-right">{{ number_format($r->teaching_hours, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
