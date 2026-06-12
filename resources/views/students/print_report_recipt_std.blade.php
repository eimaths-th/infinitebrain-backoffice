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

        .filter-chip {
            display: inline-block;
            background: #eef6ff;
            color: #155e9f;
            border: 1px solid #b7dcff;
            border-radius: 12px;
            padding: 3px 9px;
            margin: 2px;
            font-size: 10px;
            font-weight: 600;
        }

        .empty-state {
            border: 1px dashed #b8c4d0;
            border-radius: 8px;
            color: #667085;
            font-weight: 600;
            padding: 28px;
            text-align: center;
        }

        .channel-summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0 14px;
        }

        .channel-summary-card {
            border: 1px solid #dde7f1;
            border-radius: 8px;
            overflow: hidden;
        }

        .channel-summary-head {
            background: #f4f9ff;
            color: #1d4f8f;
            font-size: 11px;
            font-weight: 800;
            padding: 7px 10px;
            text-transform: uppercase;
        }

        .channel-summary-body {
            padding: 8px 10px;
        }

        .channel-count-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 8px;
        }

        .channel-count-box {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 6px;
            padding: 6px 8px;
        }

        .channel-count-box strong {
            display: block;
            color: #111827;
            font-size: 16px;
            line-height: 1;
        }

        .channel-count-box span {
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
        }

        .channel-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .channel-empty-label {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
        }

        .print-chart-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .print-chart-panel {
            border: 1px solid #dbe7f3;
            border-radius: 8px;
            overflow: hidden;
            break-inside: avoid;
        }

        .print-chart-panel-head {
            background: #f4f9ff;
            border-bottom: 1px solid #dbe7f3;
            padding: 10px 12px;
        }

        .print-chart-panel-title {
            color: #145aa5;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .print-chart-panel-note {
            color: #667085;
            font-size: 10px;
        }

        .print-chart-panel-body {
            padding: 10px 12px 12px;
        }

        .print-chart-canvas {
            height: 280px;
            position: relative;
            width: 100%;
        }

        .print-metric-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }

        .print-metric-box {
            background: #f8fafc;
            border: 1px solid #e8eef5;
            border-radius: 7px;
            padding: 7px 6px;
            text-align: center;
        }

        .print-metric-box strong {
            color: #111827;
            display: block;
            font-size: 18px;
            line-height: 1;
        }

        .print-metric-box span {
            color: #64748b;
            display: block;
            font-size: 9px;
            font-weight: 700;
            margin-top: 3px;
        }

        @media print {
            .channel-summary-grid {
                grid-template-columns: 1fr 1fr;
            }

            .print-chart-layout {
                grid-template-columns: 1fr 1fr;
            }
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
                $student_subject_first_period_dates = $student_subject_first_period_dates ?? [];

                $get_subject_enrollment_status = function ($student_id, $group, $target_year, $target_month = null) use ($student_subject_first_period_dates) {
                    $student_subject_key = $student_id . '|' . $group;
                    $first_period_info = $student_subject_first_period_dates[$student_subject_key] ?? null;

                    if ($first_period_info === null) {
                        return 'unknown';
                    }

                    $first_year = (int) ($first_period_info['year'] ?? 0);
                    $first_month = (int) ($first_period_info['month'] ?? 0);
                    $target_year = (int) $target_year;

                    if ($first_year <= 0 || $first_month <= 0) {
                        return 'unknown';
                    }

                    if ($target_month === null) {
                        if ($first_year === $target_year) {
                            return 'new';
                        }

                        return $first_year < $target_year ? 'old' : 'unknown';
                    }

                    $target_month = (int) $target_month;
                    $first_period_value = ($first_year * 100) + $first_month;
                    $target_period_value = ($target_year * 100) + $target_month;

                    if ($first_period_value === $target_period_value) {
                        return 'new';
                    }

                    return $first_period_value < $target_period_value ? 'old' : 'unknown';
                };

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
                                    'subject_enrollment_status' => $get_subject_enrollment_status($studentId, $group, $year, $demoMonth),
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
                        $new_student_count = collect($students)->where('subject_enrollment_status', 'new')->count();
                        $old_student_count = collect($students)->where('subject_enrollment_status', 'old')->count();
                        $unknown_student_count = collect($students)->where('subject_enrollment_status', 'unknown')->count();

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
                            <div class="demo-stats">
                                <div class="demo-stat">
                                    <div class="demo-stat-num" style="color: #16A34A;">{{ $new_student_count }}</div>
                                    <div class="demo-stat-label">เด็กใหม่</div>
                                </div>
                                <div class="demo-stat">
                                    <div class="demo-stat-num" style="color: #0284C7;">{{ $old_student_count }}</div>
                                    <div class="demo-stat-label">เด็กเก่า</div>
                                </div>
                                <div class="demo-stat">
                                    <div class="demo-stat-num" style="color: #6B7280;">{{ $unknown_student_count }}</div>
                                    <div class="demo-stat-label">ไม่ทราบ</div>
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

    {{-- ==================== PROMOTION / SOURCE CHART SECTION ==================== --}}
    @if (in_array('promotion-source-chart', $sections))
        @php
            $channel_students = [];

            foreach ($reportYear_schoolPrice as $student_id => $grouped_data) {
                foreach ($grouped_data as $school_price) {
                    if (!$school_price->std) {
                        continue;
                    }

                    if (!isset($channel_students[$student_id])) {
                        $channel_students[$student_id] = [
                            'promotions' => [],
                            'sources' => [],
                        ];
                    }

                    $promotion_name = $school_price->rec_school->promotion->name ?? null;
                    $source_name = $school_price->rec_school->source ?? null;

                    if (!empty($promotion_name)) {
                        $channel_students[$student_id]['promotions'][$promotion_name] = $promotion_name;
                    }

                    if (!empty($source_name)) {
                        $channel_students[$student_id]['sources'][$source_name] = $source_name;
                    }
                }
            }

            $channel_students = collect($channel_students);
            $channel_student_total = $channel_students->count();
            $channel_promotion_student_count = $channel_students->filter(function ($student) {
                return count($student['promotions'] ?? []) > 0;
            })->count();
            $channel_source_student_count = $channel_students->filter(function ($student) {
                return count($student['sources'] ?? []) > 0;
            })->count();
            $channel_promotion_name_counts = $channel_students
                ->flatMap(function ($student) {
                    return array_unique(array_values($student['promotions'] ?? []));
                })
                ->filter()
                ->countBy()
                ->sortDesc();
            $channel_source_name_counts = $channel_students
                ->flatMap(function ($student) {
                    return array_unique(array_values($student['sources'] ?? []));
                })
                ->filter()
                ->countBy()
                ->sortDesc();
            $max_promotion_count = $channel_promotion_name_counts->max() ?: 1;
            $max_source_count = $channel_source_name_counts->max() ?: 1;
            $channel_promotion_labels = $channel_promotion_name_counts->keys()->values();
            $channel_promotion_values = $channel_promotion_name_counts->values();
            $channel_source_labels = $channel_source_name_counts->keys()->values();
            $channel_source_values = $channel_source_name_counts->values();
        @endphp

        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>กราฟ Promotion / Source</h1>
                    <p>Annual Channel Overview</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📊</span> ภาพรวม Promotion / Source ทุกวิชา ปี {{ $year }}
            </div>

            @if ($channel_student_total === 0)
                <div class="empty-state">ไม่มีข้อมูลนักเรียนในปีที่เลือก</div>
            @else
                <div class="print-chart-layout">
                    <div class="print-chart-panel">
                        <div class="print-chart-panel-head">
                            <div class="print-chart-panel-title">Promotion</div>
                            <div class="print-chart-panel-note">นับนักเรียนไม่ซ้ำจากทุกวิชาในปีที่เลือก</div>
                        </div>
                        <div class="print-chart-panel-body">
                            <div class="print-metric-row">
                                <div class="print-metric-box">
                                    <strong>{{ $channel_student_total }}</strong>
                                    <span>ทั้งหมด</span>
                                </div>
                                <div class="print-metric-box">
                                    <strong>{{ $channel_promotion_student_count }}</strong>
                                    <span>มี Promotion</span>
                                </div>
                                <div class="print-metric-box">
                                    <strong>{{ $channel_student_total - $channel_promotion_student_count }}</strong>
                                    <span>ไม่มี Promotion</span>
                                </div>
                            </div>

                            @if ($channel_promotion_name_counts->isEmpty())
                                <div class="empty-state">ไม่มี Promotion ในปีที่เลือก</div>
                            @else
                                <div class="print-chart-canvas">
                                    <canvas id="promotionSourcePromotionChart"></canvas>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="print-chart-panel">
                        <div class="print-chart-panel-head">
                            <div class="print-chart-panel-title">Source</div>
                            <div class="print-chart-panel-note">นับนักเรียนไม่ซ้ำจากทุกวิชาในปีที่เลือก</div>
                        </div>
                        <div class="print-chart-panel-body">
                            <div class="print-metric-row">
                                <div class="print-metric-box">
                                    <strong>{{ $channel_student_total }}</strong>
                                    <span>ทั้งหมด</span>
                                </div>
                                <div class="print-metric-box">
                                    <strong>{{ $channel_source_student_count }}</strong>
                                    <span>มี Source</span>
                                </div>
                                <div class="print-metric-box">
                                    <strong>{{ $channel_student_total - $channel_source_student_count }}</strong>
                                    <span>ไม่มี Source</span>
                                </div>
                            </div>

                            @if ($channel_source_name_counts->isEmpty())
                                <div class="empty-state">ไม่มี Source ในปีที่เลือก</div>
                            @else
                                <div class="print-chart-canvas">
                                    <canvas id="promotionSourceSourceChart"></canvas>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== FILTERED STUDENT LIST SECTION ==================== --}}
    @if (in_array('filtered-student-list', $sections))
        @php
            $demo_group = trim((string) request()->get('demo_group', ''));
            $demo_month_param = request()->get('demo_month', 'all');
            $demo_month = $demo_month_param === 'all' ? 'all' : (int) $demo_month_param;
            if ($demo_month !== 'all' && ($demo_month < 1 || $demo_month > 12)) {
                $demo_month = 'all';
            }

            $allowed_sex_filters = ['all', 'male', 'female', 'unknown'];
            $demo_sex = request()->get('demo_sex', 'all');
            if (!in_array($demo_sex, $allowed_sex_filters, true)) {
                $demo_sex = 'all';
            }

            $allowed_enrollment_filters = ['', 'new', 'old', 'unknown'];
            $demo_enrollment = request()->get('demo_enrollment', '');
            if (!in_array($demo_enrollment, $allowed_enrollment_filters, true)) {
                $demo_enrollment = '';
            }

            $allowed_promotion_filters = ['all', 'with-promotion', 'without-promotion'];
            $demo_promotion_filter = request()->get('demo_promotion_filter', 'all');
            if (!in_array($demo_promotion_filter, $allowed_promotion_filters, true)) {
                $demo_promotion_filter = 'all';
            }

            $allowed_source_filters = ['all', 'with-source', 'without-source'];
            $demo_source_filter = request()->get('demo_source_filter', 'all');
            if (!in_array($demo_source_filter, $allowed_source_filters, true)) {
                $demo_source_filter = 'all';
            }

            $demo_promotion_name = trim((string) request()->get('demo_promotion_name', ''));
            $demo_source_name = trim((string) request()->get('demo_source_name', ''));
            $student_subject_first_period_dates = $student_subject_first_period_dates ?? [];

            $group_names = [
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

            $enrollment_labels = [
                'new' => 'ใหม่',
                'old' => 'เก่า',
                'unknown' => 'ไม่ทราบ',
            ];

            $sex_labels = [
                'male' => 'ชาย',
                'female' => 'หญิง',
                'unknown' => 'ไม่ระบุ',
            ];

            $normalize_student_sex = function ($student_sex) {
                $student_sex = strtolower(trim((string) $student_sex));
                if ($student_sex === 'ชาย' || $student_sex === 'male') {
                    return 'male';
                }
                if ($student_sex === 'หญิง' || $student_sex === 'female') {
                    return 'female';
                }
                return 'unknown';
            };

            $get_subject_enrollment_status = function ($student_id, $group, $target_year, $target_month = null) use ($student_subject_first_period_dates) {
                $student_subject_key = $student_id . '|' . $group;
                $first_period_info = $student_subject_first_period_dates[$student_subject_key] ?? null;

                if ($first_period_info === null) {
                    return 'unknown';
                }

                $first_year = (int) ($first_period_info['year'] ?? 0);
                $first_month = (int) ($first_period_info['month'] ?? 0);
                $target_year = (int) $target_year;

                if ($first_year <= 0 || $first_month <= 0) {
                    return 'unknown';
                }

                if ($target_month === null) {
                    return $first_year === $target_year ? 'new' : ($first_year < $target_year ? 'old' : 'unknown');
                }

                $target_month = (int) $target_month;
                $first_period_value = ($first_year * 100) + $first_month;
                $target_period_value = ($target_year * 100) + $target_month;

                if ($first_period_value === $target_period_value) {
                    return 'new';
                }

                return $first_period_value < $target_period_value ? 'old' : 'unknown';
            };

            $period_covers_month = function ($period, $target_year, $target_month) {
                $periods = explode('-', str_replace(' - ', '-', (string) $period));
                if (count($periods) >= 4) {
                    $start_year = (int) $periods[0];
                    $start_month = (int) $periods[1];
                    $end_year = (int) $periods[2];
                    $end_month = (int) $periods[3];
                } elseif (count($periods) >= 2) {
                    $start_year = (int) $periods[0];
                    $start_month = (int) $periods[1];
                    $end_year = $start_year;
                    $end_month = $start_month;
                } else {
                    return false;
                }

                try {
                    $check_date = Carbon::create((int) $target_year, (int) $target_month, 15);
                    $start_date = Carbon::create($start_year, $start_month, 1);
                    $end_date = Carbon::create($end_year, $end_month, 1)->endOfMonth();

                    return $check_date->between($start_date, $end_date);
                } catch (\Exception $exception) {
                    return false;
                }
            };

            $filtered_students = [];

            if ($demo_group !== '') {
                foreach ($reportYear_schoolPrice as $student_id => $grouped_data) {
                    foreach ($grouped_data as $school_price) {
                        if ($school_price->group !== $demo_group || !$school_price->std) {
                            continue;
                        }

                        if ($demo_month !== 'all' && !$period_covers_month($school_price->period, $year, $demo_month)) {
                            continue;
                        }

                        $target_month = $demo_month === 'all' ? null : $demo_month;
                        $student_sex = $normalize_student_sex($school_price->std->sex ?? null);

                        if (!isset($filtered_students[$student_id])) {
                            $filtered_students[$student_id] = [
                                'code' => $school_price->std->code ?? '-',
                                'nick_name' => $school_price->std->nick_name ?? '-',
                                'sex' => $student_sex,
                                'subject_enrollment_status' => $get_subject_enrollment_status($student_id, $demo_group, $year, $target_month),
                                'promotions' => [],
                                'sources' => [],
                            ];
                        }

                        $promotion_name = $school_price->rec_school->promotion->name ?? null;
                        $source_name = $school_price->rec_school->source ?? null;

                        if (!empty($promotion_name)) {
                            $filtered_students[$student_id]['promotions'][$promotion_name] = $promotion_name;
                        }

                        if (!empty($source_name)) {
                            $filtered_students[$student_id]['sources'][$source_name] = $source_name;
                        }
                    }
                }
            }

            $filtered_students = collect($filtered_students)
                ->filter(function ($student) use ($demo_sex, $demo_enrollment, $demo_promotion_filter, $demo_source_filter, $demo_promotion_name, $demo_source_name) {
                    $promotion_names = array_values($student['promotions'] ?? []);
                    $source_names = array_values($student['sources'] ?? []);

                    $matches_sex = $demo_sex === 'all' || ($student['sex'] ?? 'unknown') === $demo_sex;
                    $matches_enrollment = $demo_enrollment === '' || ($student['subject_enrollment_status'] ?? 'unknown') === $demo_enrollment;
                    $matches_promotion_status = $demo_promotion_filter === 'all'
                        || ($demo_promotion_filter === 'with-promotion' && count($promotion_names) > 0)
                        || ($demo_promotion_filter === 'without-promotion' && count($promotion_names) === 0);
                    $matches_source_status = $demo_source_filter === 'all'
                        || ($demo_source_filter === 'with-source' && count($source_names) > 0)
                        || ($demo_source_filter === 'without-source' && count($source_names) === 0);
                    $matches_promotion_name = $demo_promotion_name === '' || in_array($demo_promotion_name, $promotion_names, true);
                    $matches_source_name = $demo_source_name === '' || in_array($demo_source_name, $source_names, true);

                    return $matches_sex
                        && $matches_enrollment
                        && $matches_promotion_status
                        && $matches_source_status
                        && $matches_promotion_name
                        && $matches_source_name;
                })
                ->sortBy('code')
                ->values();

            $promotion_student_count = $filtered_students->filter(function ($student) {
                return count($student['promotions'] ?? []) > 0;
            })->count();
            $source_student_count = $filtered_students->filter(function ($student) {
                return count($student['sources'] ?? []) > 0;
            })->count();
            $promotion_name_counts = $filtered_students
                ->flatMap(function ($student) {
                    return array_unique(array_values($student['promotions'] ?? []));
                })
                ->filter()
                ->countBy()
                ->sortKeys();
            $source_name_counts = $filtered_students
                ->flatMap(function ($student) {
                    return array_unique(array_values($student['sources'] ?? []));
                })
                ->filter()
                ->countBy()
                ->sortKeys();

            $month_label = $demo_month === 'all'
                ? 'ภาพรวมรายปี'
                : 'เดือน ' . thaidate('F', Carbon::create($year, (int) $demo_month, 1)->format('F'));
            $filter_descriptions = [];
            $filter_descriptions[] = 'วิชา: ' . ($group_names[$demo_group] ?? ($demo_group !== '' ? $demo_group : 'ไม่ระบุ'));
            $filter_descriptions[] = 'ช่วงเวลา: ' . $month_label . ' ' . $year;
            if ($demo_sex !== 'all') {
                $filter_descriptions[] = 'เพศ: ' . ($sex_labels[$demo_sex] ?? 'ไม่ระบุ');
            }
            if ($demo_enrollment !== '') {
                $filter_descriptions[] = 'สถานะรายวิชา: ' . ($enrollment_labels[$demo_enrollment] ?? 'ไม่ทราบ');
            }
            if ($demo_promotion_filter !== 'all') {
                $filter_descriptions[] = 'Promotion: ' . ($demo_promotion_filter === 'with-promotion' ? 'มี' : 'ไม่มี');
            }
            if ($demo_promotion_name !== '') {
                $filter_descriptions[] = 'Promotion name: ' . $demo_promotion_name;
            }
            if ($demo_source_filter !== 'all') {
                $filter_descriptions[] = 'Source: ' . ($demo_source_filter === 'with-source' ? 'มี' : 'ไม่มี');
            }
            if ($demo_source_name !== '') {
                $filter_descriptions[] = 'Source name: ' . $demo_source_name;
            }
        @endphp

        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>รายชื่อนักเรียนตามตัวกรอง</h1>
                    <p>Filtered Student List</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">ปี {{ $year }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">👥</span> รายชื่อนักเรียน {{ $group_names[$demo_group] ?? $demo_group }}
            </div>

            <div style="margin-bottom: 12px;">
                @foreach ($filter_descriptions as $filter_description)
                    <span class="filter-chip">{{ $filter_description }}</span>
                @endforeach
            </div>

            @if ($filtered_students->isEmpty())
                <div class="empty-state">ไม่พบนักเรียนตามเงื่อนไขที่เลือก</div>
            @else
                <div class="channel-summary-grid">
                    <div class="channel-summary-card">
                        <div class="channel-summary-head">Promotion</div>
                        <div class="channel-summary-body">
                            <div class="channel-count-grid">
                                <div class="channel-count-box">
                                    <strong>{{ $promotion_student_count }}</strong>
                                    <span>มี Promotion</span>
                                </div>
                                <div class="channel-count-box">
                                    <strong>{{ $filtered_students->count() - $promotion_student_count }}</strong>
                                    <span>ไม่มี Promotion</span>
                                </div>
                            </div>
                            <div class="channel-chip-list">
                                @forelse ($promotion_name_counts as $promotion_name => $promotion_count)
                                    <span class="filter-chip">{{ $promotion_name }} <strong>{{ $promotion_count }}</strong></span>
                                @empty
                                    <span class="channel-empty-label">ไม่มี Promotion</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="channel-summary-card">
                        <div class="channel-summary-head">Source</div>
                        <div class="channel-summary-body">
                            <div class="channel-count-grid">
                                <div class="channel-count-box">
                                    <strong>{{ $source_student_count }}</strong>
                                    <span>มี Source</span>
                                </div>
                                <div class="channel-count-box">
                                    <strong>{{ $filtered_students->count() - $source_student_count }}</strong>
                                    <span>ไม่มี Source</span>
                                </div>
                            </div>
                            <div class="channel-chip-list">
                                @forelse ($source_name_counts as $source_name => $source_count)
                                    <span class="filter-chip">{{ $source_name }} <strong>{{ $source_count }}</strong></span>
                                @empty
                                    <span class="channel-empty-label">ไม่มี Source</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 45px;">ลำดับ</th>
                            <th style="width: 75px;">Code</th>
                            <th>ชื่อเล่น</th>
                            <th style="width: 90px;">สถานะรายวิชา</th>
                            <th style="width: 70px;">เพศ</th>
                            <th>Promotion</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filtered_students as $index => $student)
                            @php
                                $promotion_names = array_values($student['promotions'] ?? []);
                                $source_names = array_values($student['sources'] ?? []);
                                $student_status = $student['subject_enrollment_status'] ?? 'unknown';
                                $student_sex = $student['sex'] ?? 'unknown';
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{ $index + 1 }}</td>
                                <td style="text-align:center; font-weight:700;">{{ $student['code'] }}</td>
                                <td>{{ $student['nick_name'] }}</td>
                                <td style="text-align:center;">{{ $enrollment_labels[$student_status] ?? 'ไม่ทราบ' }}</td>
                                <td style="text-align:center;">{{ $sex_labels[$student_sex] ?? 'ไม่ระบุ' }}</td>
                                <td>{{ count($promotion_names) > 0 ? implode(', ', $promotion_names) : '-' }}</td>
                                <td>{{ count($source_names) > 0 ? implode(', ', $source_names) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align:right; font-weight:700;">
                                รวม {{ $filtered_students->count() }} คน
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
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

        @if (in_array('promotion-source-chart', $sections))
            const promotionSourceStudentTotal = {{ $channel_student_total ?? 0 }};
            const promotionChartLabels = {!! json_encode($channel_promotion_labels ?? []) !!};
            const promotionChartValues = {!! json_encode($channel_promotion_values ?? []) !!};
            const sourceChartLabels = {!! json_encode($channel_source_labels ?? []) !!};
            const sourceChartValues = {!! json_encode($channel_source_values ?? []) !!};

            function CreatePromotionSourceBarChart(canvas_id, labels, values, color, title) {
                const canvas = document.getElementById(canvas_id);
                if (!canvas || labels.length === 0) {
                    return;
                }

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'จำนวนนักเรียน',
                            data: values,
                            backgroundColor: color,
                            borderColor: color,
                            borderWidth: 1,
                            borderRadius: 6,
                            barThickness: 16,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: title
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.x || 0;
                                        const percent = promotionSourceStudentTotal > 0 ?
                                            Math.round((value / promotionSourceStudentTotal) * 100) : 0;
                                        return value + ' คน (' + percent + '%)';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                            y: {
                                ticks: {
                                    autoSkip: false,
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }

            CreatePromotionSourceBarChart(
                'promotionSourcePromotionChart',
                promotionChartLabels,
                promotionChartValues,
                '#22a447',
                'Top Promotion'
            );
            CreatePromotionSourceBarChart(
                'promotionSourceSourceChart',
                sourceChartLabels,
                sourceChartValues,
                '#0ea5c6',
                'Top Source'
            );
        @endif
    </script>
</body>

</html>
