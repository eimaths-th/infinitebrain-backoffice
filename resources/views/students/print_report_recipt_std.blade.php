@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานนักเรียน ปี {{ $year }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 13px;
            color: #333;
            background-color: #525659;
            padding: 0;
            margin: 0;
        }

        .page {
            width: {{ $orientation === 'landscape' ? '297mm' : '210mm' }};
            min-height: {{ $orientation === 'landscape' ? '210mm' : '297mm' }};
            padding: 12mm 15mm;
            margin: 10mm auto;
            border: 1px solid #D3D3D3;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        @media print {
            @page {
                size: A4 {{ $orientation }};
                margin: 8mm;
            }

            body {
                background: none;
                margin: 0;
            }

            .page {
                margin: 0;
                border: none;
                border-radius: 0;
                width: 100%;
                min-height: auto;
                box-shadow: none;
                background: white;
                page-break-after: always;
                padding: 5mm 10mm;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .no-print {
                display: none !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Header */
        .report-header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #2196F3;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .report-header .logo-area {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2196F3, #1565C0);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .report-header .header-text h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1565C0;
            margin-bottom: 2px;
        }

        .report-header .header-text p {
            font-size: 12px;
            color: #666;
        }

        .report-header .header-right {
            margin-left: auto;
            text-align: right;
        }

        .report-header .header-right .year-badge {
            display: inline-block;
            background: linear-gradient(135deg, #2196F3, #1565C0);
            color: white;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
        }

        .report-header .header-right .print-date {
            font-size: 10px;
            color: #999;
            margin-top: 4px;
        }

        /* Section titles */
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1565C0;
            margin: 20px 0 10px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #e3f2fd;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title .icon {
            width: 28px;
            height: 28px;
            background: #e3f2fd;
            color: #1565C0;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11px;
        }

        th {
            background: #1565C0;
            color: white;
            font-weight: 600;
            padding: 6px 8px;
            text-align: center;
            font-size: 10px;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
        }

        tbody tr:nth-child(even) {
            background: #f9fbfd;
        }

        tfoot th,
        tfoot td {
            background: #e8eaf6;
            color: #333;
            font-weight: 700;
        }

        /* Timeline */
        .timeline-container {
            margin-bottom: 12px;
        }

        .timeline-row {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
            padding: 2px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .timeline-label {
            width: 100px;
            font-size: 11px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .timeline-label .student-code {
            font-size: 9px;
            color: #999;
            font-weight: 400;
        }

        .timeline-bars {
            flex: 1;
            display: flex;
            position: relative;
            background: #f8f9fa;
            border-radius: 4px;
            flex-direction: column;
            gap: 2px;
            padding: 2px 0;
        }

        .timeline-bars-inner {
            position: relative;
            height: 18px;
            display: flex;
            width: 100%;
        }

        .timeline-month-grid {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
        }

        .timeline-month {
            flex: 1;
            border-right: 1px solid #e9ecef;
            position: relative;
        }

        .timeline-month:last-child {
            border-right: none;
        }

        .timeline-bar {
            position: absolute;
            height: 16px;
            top: 1px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: white;
            font-weight: 600;
            z-index: 1;
        }

        .timeline-months-header {
            display: flex;
            margin-left: 100px;
            margin-bottom: 4px;
        }

        .timeline-months-header span {
            flex: 1;
            text-align: center;
            font-size: 9px;
            color: #999;
            font-weight: 600;
        }

        /* Group colors */
        .group-M {
            background: #4CAF50;
        }

        .group-E {
            background: #2196F3;
        }

        .group-C,
        .group-CHN {
            background: #FF9800;
        }

        .group-S,
        .group-Sci {
            background: #9C27B0;
        }

        .group-T {
            background: #00BCD4;
        }

        .group-J {
            background: #E91E63;
        }

        .group-K {
            background: #795548;
        }

        /* Legend */
        .legend {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin: 8px 0 16px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
        }

        .legend-color {
            width: 16px;
            height: 10px;
            border-radius: 3px;
        }

        /* Demographics */
        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        @media print {
            .demo-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .demo-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .demo-card-head {
            padding: 8px 12px;
            color: white;
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .demo-card-head .badge {
            background: rgba(255, 255, 255, .25);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: auto;
        }

        .demo-card-body {
            padding: 10px 12px;
            background: #fff;
        }

        .demo-stats {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .demo-stat {
            flex: 1;
            text-align: center;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 6px 4px;
        }

        .demo-stat-num {
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
        }

        .demo-stat-label {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }

        .demo-bar-wrap {
            margin-bottom: 4px;
        }

        .demo-bar-label {
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .demo-bar-bg {
            background: #f0f0f0;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .demo-bar-fill {
            height: 100%;
            border-radius: 10px;
        }

        .demo-sub-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #888;
            margin: 6px 0 4px 0;
        }

        .demo-pill {
            display: inline-block;
            border-radius: 12px;
            padding: 1px 8px;
            font-size: 10px;
            font-weight: 600;
            margin: 2px;
        }

        /* Print controls */
        .print-controls {
            position: fixed;
            top: 10px;
            right: 20px;
            z-index: 9999;
            background: rgba(0, 0, 0, .8);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .print-controls button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .print-controls button:hover {
            background: #388E3C;
        }

        .print-controls .btn-close-print {
            background: #757575;
            margin-left: 8px;
        }

        .print-controls .btn-close-print:hover {
            background: #616161;
        }

        /* Paper table */
        .paper-table th {
            background: #37474F;
        }
    </style>
</head>

<body>
    {{-- Print controls --}}
    <div class="print-controls no-print">
        <span>📄 {{ $orientation === 'landscape' ? 'แนวนอน' : 'แนวตั้ง' }} (A4)</span>
        <button onclick="window.print()">🖨️ พิมพ์</button>
        <button class="btn-close-print" onclick="window.close()">✕ ปิด</button>
    </div>

    {{-- ==================== MONTHLY CHARTS SECTION ==================== --}}
    @if (in_array('all-month-chart', $sections))
        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายงานนักเรียน Infinite Brain</h1>
                    <p>Annual Summary Chart — Performance Analysis</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📈</span> กราฟสรุปรายเดือน (All Month Report)
            </div>

            <div style="margin-bottom: 30px; height: 350px;">
                <canvas id="barChartPrint"></canvas>
            </div>

            <div style="height: 350px;">
                <canvas id="lineChartPrint"></canvas>
            </div>
        </div>
    @endif

    {{-- ==================== TIMELINE SECTION ==================== --}}
    @if (in_array('timeline', $sections))
        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายงานนักเรียน Infinite Brain</h1>
                    <p>Student Enrollment Report — Timeline View</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📊</span> Timeline นักเรียนรายปี {{ $year }}
            </div>

            {{-- Legend --}}
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color group-M"></div><span>M - คณิตศาสตร์</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color group-E"></div><span>E - ภาษาอังกฤษ</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color group-C"></div><span>C - ภาษาจีน</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color group-S"></div><span>S - วิทยาศาสตร์</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color group-T"></div><span>T - ภาษาไทย</span>
                </div>
            </div>

            {{-- Month headers --}}
            <div class="timeline-months-header">
                @for ($m = 1; $m <= 12; $m++)
                    <span>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</span>
                @endfor
            </div>

            {{-- Timeline rows --}}
            <div class="timeline-container">
                @foreach ($reportYear_schoolPrice as $studentId => $groupedData)
                    @php
                        $studentCode = $groupedData->first()->std->code ?? 'N/A';
                        $studentName = $groupedData->first()->std->nick_name ?? 'Unknown';

                        // Group bars by subject
                        $timelineBars = [];
                        foreach ($groupedData as $data1) {
                            $period = explode(' - ', str_replace(' - ', '-', $data1->period));
                            if (count($period) == 1) {
                                $periods = explode('-', $data1->period);
                                if (count($periods) >= 2) {
                                    $startYear = intval($periods[0]);
                                    $startMonth = intval($periods[1]);
                                    $endYear = count($periods) >= 4 ? intval($periods[2]) : $startYear;
                                    $endMonth = count($periods) >= 4 ? intval($periods[3]) : $startMonth;
                                } else {
                                    continue;
                                }
                            } else {
                                try {
                                    $startDate = Carbon::parse($period[0]);
                                    $endDate = Carbon::parse($period[1]);
                                    $startMonth = $startDate->month;
                                    $endMonth = $endDate->month;
                                    $startYear = $startDate->year;
                                    $endYear = $endDate->year;
                                } catch (\Exception $e) {
                                    continue;
                                }
                            }

                            $group = $data1->group;
                            $groupMark = $group === 'CHN' ? 'C' : ($group === 'Sci' ? 'S' : $group);

                            $yearInt = (int) $year;
                            if (($startYear <= $yearInt && $endYear >= $yearInt)) {
                                $barStart = $startYear < $yearInt ? 1 : $startMonth;
                                $barEnd = $endYear > $yearInt ? 12 : $endMonth;

                                if (!isset($timelineBars[$groupMark])) {
                                    $timelineBars[$groupMark] = [];
                                }

                                $timelineBars[$groupMark][] = [
                                    'start' => $barStart,
                                    'end' => $barEnd,
                                    'group' => $groupMark,
                                ];
                            }
                        }
                    @endphp
                    <div class="timeline-row">
                        <div class="timeline-label">
                            <div>{{ $studentName }}</div>
                            <div class="student-code">{{ $studentCode }}</div>
                        </div>
                        <div class="timeline-bars">
                            @foreach ($timelineBars as $groupMark => $bars)
                                <div class="timeline-bars-inner">
                                    <div class="timeline-month-grid">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <div class="timeline-month"></div>
                                        @endfor
                                    </div>
                                    @foreach ($bars as $bar)
                                        @php
                                            $left = (($bar['start'] - 1) / 12) * 100;
                                            $width = (($bar['end'] - $bar['start'] + 1) / 12) * 100;
                                        @endphp
                                        <div class="timeline-bar group-{{ $bar['group'] }}"
                                            style="left: {{ $left }}%; width: {{ $width }}%;">
                                            {{ $bar['group'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ==================== SUMMARY TABLE SECTION ==================== --}}
    @if (in_array('summary-table', $sections))
        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายงานนักเรียน Infinite Brain</h1>
                    <p>Student Enrollment Summary — Monthly Breakdown</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📋</span> สรุปจำนวนนักเรียนแต่ละวิชาต่อเดือน
            </div>

            @php
                $summaryData = [];
                $subject_yearly_unique = [];
                $allGroups = [];
                foreach ($reportYear_schoolPrice as $studentId => $groupedData) {
                    foreach ($groupedData as $data1) {
                        $group = $data1->group;
                        if (!in_array($group, $allGroups)) {
                            $allGroups[] = $group;
                        }
                        if ($data1->std && !isset($subject_yearly_unique[$group][$studentId])) {
                            $subject_yearly_unique[$group][$studentId] = true;
                        }

                        $period = $data1->period;
                        $periods = explode('-', str_replace(' - ', '-', $period));
                        if (count($periods) >= 4) {
                            $startYear = intval($periods[0]);
                            $startMonth = intval($periods[1]);
                            $endYear = intval($periods[2]);
                            $endMonth = intval($periods[3]);
                        } elseif (count($periods) >= 2) {
                            $startYear = intval($periods[0]);
                            $startMonth = intval($periods[1]);
                            $endYear = $startYear;
                            $endMonth = $startMonth;
                        } else {
                            continue;
                        }

                        for ($m = 1; $m <= 12; $m++) {
                            $checkDate = Carbon::create($year, $m, 15);
                            $startDate = Carbon::create($startYear, $startMonth, 1);
                            $endDate = Carbon::create($endYear, $endMonth, 1)->endOfMonth();
                            if ($checkDate->between($startDate, $endDate)) {
                                $key = $group . '_' . $m . '_' . $studentId;
                                $summaryData[$group][$m][$studentId] = true;
                            }
                        }
                    }
                }
                sort($allGroups);
            @endphp

            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">วิชา</th>
                        @for ($m = 1; $m <= 12; $m++)
                            <th>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</th>
                        @endfor
                        <th style="background: #FF9800;">รวมรายเดือนสะสม</th>
                        <th style="background: #4CAF50;">นักเรียนไม่ซ้ำทั้งปี</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $monthTotals = array_fill(1, 12, 0);
                        $yearly_unique_total = 0;
                    @endphp
                    @foreach ($allGroups as $group)
                        @php
                            $rowTotal = 0;
                            $yearly_unique_count = isset($subject_yearly_unique[$group])
                                ? count($subject_yearly_unique[$group])
                                : 0;
                            $yearly_unique_total += $yearly_unique_count;
                        @endphp
                        <tr>
                            <td class="text-center" style="font-weight:700; text-align:center;">{{ $group }}
                            </td>
                            @for ($m = 1; $m <= 12; $m++)
                                @php
                                    $count = isset($summaryData[$group][$m]) ? count($summaryData[$group][$m]) : 0;
                                    $rowTotal += $count;
                                    $monthTotals[$m] += $count;
                                @endphp
                                <td
                                    style="text-align:center; {{ $count > 0 ? 'color:#4CAF50; font-weight:700;' : 'color:#ccc;' }}">
                                    {{ $count > 0 ? $count : '-' }}
                                </td>
                            @endfor
                            <td style="text-align:center; font-weight:700; background:#fff8e1;">{{ $rowTotal }}
                            </td>
                            <td style="text-align:center; font-weight:700; background:#e8f5e9;">
                                {{ $yearly_unique_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align:center; font-weight:700;">รวม</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td style="text-align:center; font-weight:700;">
                                {{ $monthTotals[$m] > 0 ? $monthTotals[$m] : '-' }}</td>
                        @endfor
                        <td style="text-align:center; font-weight:700; background:#FF9800; color:#fff;">
                            {{ array_sum($monthTotals) }}</td>
                        <td style="text-align:center; font-weight:700; background:#4CAF50; color:#fff;">
                            {{ $yearly_unique_total }}</td>
                    </tr>
                </tfoot>
            </table>
            <div style="margin-top: 10px; font-size: 10px; color: #555;">
                หมายเหตุ: รวมรายเดือนสะสมคือผลรวมของแต่ละเดือน ส่วนนักเรียนไม่ซ้ำทั้งปีนับไม่ซ้ำต่อวิชา
                และใช้เทียบกับการ์ดข้อมูลประชากรรายปี
            </div>
        </div>
    @endif

    {{-- ==================== DEMOGRAPHICS SECTION ==================== --}}
    @if (in_array('demographics', $sections))
        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายงานนักเรียน Infinite Brain</h1>
                    <p>Student Demographics Analysis</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">👥</span> ข้อมูลประชากรนักเรียนแต่ละวิชา
            </div>

            @php
                $demoMonthParam = request()->get('demo_month', 'all');
                $isAnnual = $demoMonthParam === 'all';
                $demoMonth = $isAnnual ? null : (int) $demoMonthParam;
                $demoData = [];

                foreach ($reportYear_schoolPrice as $studentId => $groupedData) {
                    foreach ($groupedData as $data1) {
                        $period = $data1->period;
                        $group = $data1->group;
                        $std = $data1->std;
                        if (!$std) {
                            continue;
                        }

                        $isIncluded = false;
                        if ($isAnnual) {
                            $isIncluded = true;
                        } else {
                            $periods = explode('-', str_replace(' - ', '-', $period));
                            if (count($periods) >= 4) {
                                $startYear = (int) $periods[0];
                                $startMonth = (int) $periods[1];
                                $endYear = (int) $periods[2];
                                $endMonth = (int) $periods[3];
                            } elseif (count($periods) >= 2) {
                                $startYear = (int) $periods[0];
                                $startMonth = (int) $periods[1];
                                $endYear = $startYear;
                                $endMonth = $startMonth;
                            } else {
                                continue;
                            }

                            $checkDate = Carbon::create($year, $demoMonth, 15);
                            $startDate = Carbon::create($startYear, $startMonth, 1);
                            $endDate = Carbon::create($endYear, $endMonth, 1)->endOfMonth();
                            if ($checkDate->between($startDate, $endDate)) {
                                $isIncluded = true;
                            }
                        }

                        if ($isIncluded) {
                            if (!isset($demoData[$group][$studentId])) {
                                $age = null;
                                if ($std->birth_date) {
                                    try {
                                        $age = Carbon::parse($std->birth_date)->age;
                                    } catch (\Exception $e) {
                                    }
                                }
                                $demoData[$group][$studentId] = [
                                    'age' => $age,
                                    'sex' => $std->sex ?? null,
                                    'grade' => $std->grade ?? null,
                                ];
                            }
                        }
                    }
                }

                $groupColors = [
                    'M' => '#4CAF50',
                    'E' => '#2196F3',
                    'C' => '#FF9800',
                    'CHN' => '#FF9800',
                    'S' => '#9C27B0',
                    'Sci' => '#9C27B0',
                    'T' => '#00BCD4',
                    'J' => '#E91E63',
                    'K' => '#795548',
                ];
                $groupNames = [
                    'M' => 'M - คณิตศาสตร์',
                    'E' => 'E - ภาษาอังกฤษ',
                    'C' => 'C - ภาษาจีน',
                    'CHN' => 'C - ภาษาจีน',
                    'S' => 'S - วิทยาศาสตร์',
                    'Sci' => 'S - วิทยาศาสตร์',
                    'T' => 'T - ภาษาไทย',
                    'J' => 'J - ภาษาญี่ปุ่น',
                    'K' => 'K - ภาษาเกาหลี',
                ];
            @endphp

            <div class="mb-3 text-center">
                <span class="badge bg-dark p-2 px-3 fs-6" style="color: #fff;">
                    @if ($isAnnual)
                        สรุปภาพรวมรายปี {{ $year }}
                    @else
                        ประจำเดือน {{ thaidate('F', Carbon::create($year, (int) $demoMonth, 1)->format('F')) }}
                        {{ $year }}
                    @endif
                </span>
            </div>

            <div class="demo-grid">
                @foreach ($demoData as $group => $students)
                    @php
                        $color = $groupColors[$group] ?? '#607D8B';
                        $groupName = $groupNames[$group] ?? $group;
                        $total = count($students);
                        $maleCount = collect($students)->where('sex', 'ชาย')->count();
                        $femaleCount = collect($students)->where('sex', 'หญิง')->count();
                        $noSex = $total - $maleCount - $femaleCount;

                        $ageRanges = ['< 7' => 0, '7-9' => 0, '10-12' => 0, '13-15' => 0, '> 15' => 0, 'ไม่ระบุ' => 0];
                        foreach ($students as $s) {
                            $a = $s['age'];
                            if ($a === null) {
                                $ageRanges['ไม่ระบุ']++;
                            } elseif ($a < 7) {
                                $ageRanges['< 7']++;
                            } elseif ($a <= 9) {
                                $ageRanges['7-9']++;
                            } elseif ($a <= 12) {
                                $ageRanges['10-12']++;
                            } elseif ($a <= 15) {
                                $ageRanges['13-15']++;
                            } else {
                                $ageRanges['> 15']++;
                            }
                        }

                        $showGrade = in_array($group, ['M', 'E']);
                        $grades = [];
                        if ($showGrade) {
                            foreach ($students as $s) {
                                $g = trim($s['grade'] ?? 'ไม่ระบุ');
                                if ($g === '' || $g === null) {
                                    $g = 'ไม่ระบุ';
                                }
                                $grades[$g] = ($grades[$g] ?? 0) + 1;
                            }
                            arsort($grades);
                        }
                        $maxAge = max(array_values($ageRanges)) ?: 1;
                    @endphp
                    <div class="demo-card">
                        <div class="demo-card-head" style="background: {{ $color }};">
                            {{ $groupName }}
                            <span class="badge">{{ $total }} คน</span>
                        </div>
                        <div class="demo-card-body">
                            <div class="demo-stats">
                                <div class="demo-stat">
                                    <div class="demo-stat-num" style="color: {{ $color }};">{{ $total }}
                                    </div>
                                    <div class="demo-stat-label">ทั้งหมด</div>
                                </div>
                                <div class="demo-stat">
                                    <div class="demo-stat-num" style="color: #2196F3;">{{ $maleCount }}</div>
                                    <div class="demo-stat-label">ชาย</div>
                                </div>
                                <div class="demo-stat">
                                    <div class="demo-stat-num" style="color: #E91E63;">{{ $femaleCount }}</div>
                                    <div class="demo-stat-label">หญิง</div>
                                </div>
                            </div>

                            @if ($total > 0)
                                <div class="demo-sub-title">เพศ</div>
                                @php
                                    $malePct = round(($maleCount / $total) * 100);
                                    $femalePct = round(($femaleCount / $total) * 100);
                                @endphp
                                <div class="demo-bar-wrap">
                                    <div class="demo-bar-label"><span>ชาย</span><span>{{ $maleCount }}
                                            ({{ $malePct }}%)
                                        </span></div>
                                    <div class="demo-bar-bg">
                                        <div class="demo-bar-fill"
                                            style="width:{{ $malePct }}%; background:#2196F3;"></div>
                                    </div>
                                </div>
                                <div class="demo-bar-wrap">
                                    <div class="demo-bar-label"><span>หญิง</span><span>{{ $femaleCount }}
                                            ({{ $femalePct }}%)</span></div>
                                    <div class="demo-bar-bg">
                                        <div class="demo-bar-fill"
                                            style="width:{{ $femalePct }}%; background:#E91E63;"></div>
                                    </div>
                                </div>

                                <div class="demo-sub-title">ช่วงอายุ</div>
                                @foreach ($ageRanges as $label => $cnt)
                                    @if ($cnt > 0)
                                        <div class="demo-bar-wrap">
                                            <div class="demo-bar-label">
                                                <span>{{ $label }}</span><span>{{ $cnt }}</span>
                                            </div>
                                            <div class="demo-bar-bg">
                                                <div class="demo-bar-fill"
                                                    style="width:{{ round(($cnt / $maxAge) * 100) }}%; background:{{ $color }};">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if ($showGrade && count($grades) > 0)
                                    <div class="demo-sub-title">ชั้นเรียน</div>
                                    <div>
                                        @foreach ($grades as $grade => $cnt)
                                            <span class="demo-pill"
                                                style="border: 1.5px solid {{ $color }}; color: {{ $color }};">
                                                {{ $grade }} <strong>{{ $cnt }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ==================== MONTHLY TABLE SECTION ==================== --}}
    @if (in_array('monthly-table', $sections))
        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายงานนักเรียน Infinite Brain</h1>
                    <p>Monthly Registration Details</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📝</span> ตารางค่าสมัครเรียนเดือน {{ thaidate('F', $month) }}
                {{ $year }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>ชื่อเล่น</th>
                        <th>วันสมัคร</th>
                        <th>Period</th>
                        <th>เลขที่ใบเสร็จ</th>
                        <th>วิชา</th>
                        <th>ยอดเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_amount = 0;
                        $row = 0;
                    @endphp
                    @foreach ($reportMonth_schoolPrice as $studentId => $groupedData)
                        @foreach ($groupedData as $key => $data)
                            @php $row++; @endphp
                            <tr>
                                <td style="text-align:center;">{{ $row }}</td>
                                <td style="text-align:center;">{{ $data->std->code ?? '-' }}</td>
                                <td>{{ $data->std->nick_name ?? '-' }}</td>
                                <td>{{ $data->rec_school->submission_date ?? '-' }}</td>
                                <td>{{ $data->period }}</td>
                                <td>{{ $data->rec_school->receipt_number ?? '-' }}</td>
                                <td style="text-align:center;">{{ $data->group }}</td>
                                <td style="text-align:right;">{{ number_format($data->total) }}</td>
                            </tr>
                            @php $total_amount += $data->total; @endphp
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align:center; font-weight:700;">รวมทั้งสิ้น</td>
                        <td style="text-align:right; font-weight:700;">{{ number_format($total_amount) }} บาท</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- ==================== PAPER TABLE SECTION ==================== --}}
    @if (in_array('paper-table', $sections))
        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายงานนักเรียน Infinite Brain</h1>
                    <p>Cover Sheet Record (บันทึกใบปะหน้า)</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📄</span> บันทึกใบปะหน้าประจำเดือน {{ thaidate('F', date('F')) }}
                {{ date('Y') }}
            </div>

            <table>
                <thead class="paper-table">
                    <tr>
                        <th>#</th>
                        <th>NO.</th>
                        <th>วันสมัคร</th>
                        <th>ชื่อเล่น</th>
                        <th>เลขที่ใบเสร็จ</th>
                        <th>Period</th>
                        <th>วิชา</th>
                        <th>ยอดเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paper as $key => $paper_rep)
                        <tr>
                            <td style="text-align:center;">{{ $key + 1 }}</td>
                            <td style="text-align:center;">{{ $paper_rep->no }}</td>
                            <td>{{ $paper_rep->submission_date }}</td>
                            <td>{{ $paper_rep->student->nick_name }}</td>
                            <td>{{ $paper_rep->receipt_number }}</td>
                            <td>{{ $paper_rep->school_price->period ?? null }}</td>
                            <td style="text-align:center;">{{ $paper_rep->school_price->group ?? null }}</td>
                            <td style="text-align:right;">{{ number_format($paper_rep->school_price->total ?? 0) }}
                                บาท</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        // Chart.js initialization for print page
        @if (in_array('all-month-chart', $sections))
            @php
                $pctLabels = [];
                $pctValues = [];
                foreach ($totalForAllMonths as $monthYear => $total) {
                    $pctValues[] = $total;
                    if (isset($percentageChangeForAllMonths[$monthYear])) {
                        $pct = $percentageChangeForAllMonths[$monthYear];
                        $sign = $pct > 0 ? '+' : '';
                        $pctLabels[] = $monthYear . ' ' . $sign . $pct . '%';
                    } else {
                        $pctLabels[] = $monthYear;
                    }
                }
            @endphp

            const pctLabels = {!! json_encode($pctLabels) !!};
            const pctValues = {!! json_encode($pctValues) !!};

            // Bar Chart
            new Chart(document.getElementById('barChartPrint'), {
                type: 'bar',
                data: {
                    labels: pctLabels,
                    datasets: [{
                        label: 'ยอดรวมแต่ละเดือน',
                        data: pctValues,
                        backgroundColor: '#7cb5ec',
                        borderColor: '#7cb5ec',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animation for print
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'ALL MONTH REPORT (Bar)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Line Chart
            new Chart(document.getElementById('lineChartPrint'), {
                type: 'line',
                data: {
                    labels: pctLabels,
                    datasets: [{
                        label: 'ยอดรวมแต่ละเดือน',
                        data: pctValues,
                        borderColor: '#7cb5ec',
                        backgroundColor: 'rgba(124, 181, 236, 0.2)',
                        fill: true,
                        tension: 0.1,
                        pointRadius: 4,
                        pointBackgroundColor: '#7cb5ec'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animation for print
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'ALL MONTH REPORT (Line)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    </script>
</body>

</html>
