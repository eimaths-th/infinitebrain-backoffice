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
            grid-template-columns: 1fr;
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

        .promotion-detail-table-wrap {
            margin-top: 12px;
            max-height: 220px;
            overflow: hidden;
        }

        .promotion-detail-title {
            color: #14532d;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .promotion-detail-table {
            border-collapse: collapse;
            font-size: 9px;
            width: 100%;
        }

        .promotion-detail-table th {
            background: #edf7ee;
            color: #14532d;
            font-weight: 800;
            padding: 5px 6px;
            text-align: left;
        }

        .promotion-detail-table td {
            border-bottom: 1px solid #edf2f7;
            color: #334155;
            padding: 5px 6px;
            vertical-align: top;
        }

        .promotion-detail-table td:last-child {
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
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

        .channel-bar-summary {
            align-items: baseline;
            display: flex;
            gap: 7px;
            margin-bottom: 8px;
        }

        .channel-bar-pct {
            color: #111827;
            font-size: 22px;
            font-weight: 800;
            line-height: 1;
        }

        .channel-bar-pct-label {
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
        }

        .channel-bar-canvas {
            height: 260px;
            margin-bottom: 14px;
            position: relative;
            width: 100%;
        }

        .channel-rank-title {
            color: #145aa5;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .channel-rank-note {
            color: #94a3b8;
            font-size: 9px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .channel-rank-item {
            align-items: center;
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .channel-rank-main {
            flex: 1;
            min-width: 0;
        }

        .channel-rank-head {
            align-items: baseline;
            display: flex;
            gap: 6px;
            margin-bottom: 3px;
        }

        .channel-rank-no {
            color: #94a3b8;
            flex: 0 0 16px;
            font-size: 9px;
            font-weight: 800;
        }

        .channel-rank-name {
            color: #334155;
            font-size: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .channel-rank-track {
            background: #eef2f7;
            border-radius: 999px;
            height: 9px;
            overflow: hidden;
        }

        .channel-rank-fill {
            border-radius: 999px;
            display: block;
            height: 100%;
        }

        .channel-rank-val {
            color: #111827;
            flex: 0 0 30px;
            font-size: 12px;
            font-weight: 800;
            text-align: right;
        }

        .channel-rank-item.is-muted .channel-rank-name {
            color: #9aa6b2;
            font-style: italic;
        }

        .channel-rank-item.is-muted .channel-rank-fill {
            background: #cbd5e1 !important;
        }

        @media print {
            .channel-summary-grid {
                grid-template-columns: 1fr 1fr;
            }

            .print-chart-layout {
                grid-template-columns: 1fr;
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
            $channel_school_price_collection = $channel_school_prices ?? collect();
            $parsed_channel_school_prices = [];
            $latest_channel_year = (int) ($year ?? date('Y'));

            $channel_demo_month_param = request()->get('demo_month', 'all');
            $channel_demo_month = $channel_demo_month_param === 'all' ? 'all' : (int) $channel_demo_month_param;
            if ($channel_demo_month !== 'all' && ($channel_demo_month < 1 || $channel_demo_month > 12)) {
                $channel_demo_month = 'all';
            }

            $channel_period_label = $channel_demo_month === 'all'
                ? 'ปี ' . $latest_channel_year
                : 'เดือน ' . thaidate('F', Carbon::create($latest_channel_year, (int) $channel_demo_month, 1)->format('F')) . ' ' . $latest_channel_year;

            foreach ($channel_school_price_collection as $school_price) {
                if (!$school_price->std || !$school_price->rec_school || empty($school_price->rec_school->submission_date)) {
                    continue;
                }

                try {
                    $submission_date = Carbon::parse($school_price->rec_school->submission_date);
                } catch (\Exception $exception) {
                    continue;
                }

                $student_id = $school_price->student_id;

                if (empty($student_id)) {
                    continue;
                }

                $submission_year = (int) $submission_date->format('Y');
                $month_key = $submission_date->format('Y-m');

                $parsed_channel_school_prices[] = [
                    'school_price' => $school_price,
                    'student_id' => $student_id,
                    'year' => $submission_year,
                    'month_key' => $month_key,
                ];
            }

            $channel_students = [];
            $channel_month_students = [];
            $channel_month_promotion_students = [];
            $channel_month_source_students = [];

            if ($latest_channel_year !== null) {
                $channel_month_start = $channel_demo_month === 'all' ? 1 : $channel_demo_month;
                $channel_month_end = $channel_demo_month === 'all' ? 12 : $channel_demo_month;

                for ($month_number = $channel_month_start; $month_number <= $channel_month_end; $month_number++) {
                    $month_key = sprintf('%04d-%02d', $latest_channel_year, $month_number);
                    $channel_month_students[$month_key] = [];
                    $channel_month_promotion_students[$month_key] = [];
                    $channel_month_source_students[$month_key] = [];
                }

                foreach ($parsed_channel_school_prices as $parsed_school_price) {
                    if ($parsed_school_price['year'] !== $latest_channel_year) {
                        continue;
                    }

                    if ($channel_demo_month !== 'all' && (int) Carbon::parse($parsed_school_price['month_key'] . '-01')->format('n') !== $channel_demo_month) {
                        continue;
                    }

                    $school_price = $parsed_school_price['school_price'];
                    $student_id = $parsed_school_price['student_id'];
                    $month_key = $parsed_school_price['month_key'];

                    if (!isset($channel_students[$student_id])) {
                        $channel_students[$student_id] = [
                            'promotions' => [],
                            'all_promotions' => [],
                            'sources' => [],
                        ];
                    }

                    if (!isset($channel_month_students[$month_key][$student_id])) {
                        $channel_month_students[$month_key][$student_id] = [
                            'has_promotion' => false,
                            'has_source' => false,
                        ];
                    }

                    $promotion_name = trim((string) ($school_price->rec_school->promotion->name ?? ''));
                    $source_name = trim((string) ($school_price->rec_school->source ?? ''));

                    // "No Promotion" ถือว่าไม่ได้ใช้โปรโมชั่น (ไม่นับเป็น "มี Promotion")
                    $is_real_promotion = $promotion_name !== '' && !str_contains(strtolower($promotion_name), 'no promotion');

                    if ($promotion_name !== '') {
                        // เก็บทุกชื่อ (รวม No Promotion) ไว้สำหรับกราฟ/ลิสต์
                        $channel_students[$student_id]['all_promotions'][$promotion_name] = $promotion_name;
                        $channel_month_promotion_students[$month_key][$promotion_name][$student_id] = true;

                        if ($is_real_promotion) {
                            // นับเป็น "มี Promotion" เฉพาะโปรจริง (ไม่รวม No Promotion)
                            $channel_students[$student_id]['promotions'][$promotion_name] = $promotion_name;
                            $channel_month_students[$month_key][$student_id]['has_promotion'] = true;
                        }
                    }

                    if ($source_name !== '') {
                        $channel_students[$student_id]['sources'][$source_name] = $source_name;
                        $channel_month_students[$month_key][$student_id]['has_source'] = true;
                        $channel_month_source_students[$month_key][$source_name][$student_id] = true;
                    }
                }
            }

            ksort($channel_month_students);
            ksort($channel_month_promotion_students);
            ksort($channel_month_source_students);

            $channel_students = collect($channel_students);
            $channel_student_total = $channel_students->count();
            $channel_promotion_student_count = $channel_students->filter(function ($student) {
                return count($student['promotions'] ?? []) > 0;
            })->count();
            $channel_source_student_count = $channel_students->filter(function ($student) {
                return count($student['sources'] ?? []) > 0;
            })->count();
            $channel_month_labels = collect(array_keys($channel_month_students))->values();
            $channel_month_total_values = collect($channel_month_students)
                ->map(function ($month_students) {
                    return count($month_students);
                })
                ->values();
            $channel_promotion_month_values = collect($channel_month_students)
                ->map(function ($month_students) {
                    return collect($month_students)
                        ->filter(function ($student) {
                            return $student['has_promotion'] ?? false;
                        })
                        ->count();
                })
                ->values();
            $channel_source_month_values = collect($channel_month_students)
                ->map(function ($month_students) {
                    return collect($month_students)
                        ->filter(function ($student) {
                            return $student['has_source'] ?? false;
                        })
                        ->count();
                })
                ->values();
            $channel_month_promotion_rows = collect($channel_month_promotion_students)
                ->flatMap(function ($promotion_students_by_name, $month_key) {
                    ksort($promotion_students_by_name);

                    return collect($promotion_students_by_name)
                        ->map(function ($student_ids, $promotion_name) use ($month_key) {
                            return [
                                'month' => $month_key,
                                'promotion_name' => $promotion_name,
                                'student_count' => count($student_ids),
                            ];
                        })
                        ->values();
                })
                ->values();

            // อันดับ Promotion / Source (นับนักเรียนไม่ซ้ำต่อชื่อ)
            $channel_promotion_ranking = [];
            $channel_source_ranking = [];
            foreach ($channel_students as $channel_student) {
                foreach ($channel_student['all_promotions'] ?? [] as $promotion_name) {
                    $channel_promotion_ranking[$promotion_name] = ($channel_promotion_ranking[$promotion_name] ?? 0) + 1;
                }
                foreach ($channel_student['sources'] ?? [] as $source_name) {
                    $channel_source_ranking[$source_name] = ($channel_source_ranking[$source_name] ?? 0) + 1;
                }
            }
            arsort($channel_promotion_ranking);
            arsort($channel_source_ranking);
            $channel_promotion_rank_max = count($channel_promotion_ranking) ? max($channel_promotion_ranking) : 0;
            $channel_source_rank_max = count($channel_source_ranking) ? max($channel_source_ranking) : 0;
            $channel_promotion_percent = $channel_student_total > 0 ? round(($channel_promotion_student_count / $channel_student_total) * 100) : 0;
            $channel_source_percent = $channel_student_total > 0 ? round(($channel_source_student_count / $channel_student_total) * 100) : 0;
            $channel_bar_palette = [
                '#22a447', '#0ea5c6', '#f59e0b', '#ef4444', '#8b5cf6',
                '#ec4899', '#14b8a6', '#6366f1', '#84cc16', '#f97316',
                '#0891b2', '#a855f7', '#e11d48', '#65a30d', '#2563eb', '#d97706',
            ];

            // ป้ายแกน X = เดือนย่อภาษาไทย
            $thai_month_abbr = [
                1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
                5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
                9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
            ];
            $channel_month_keys = array_keys($channel_month_students);
            $channel_month_chart_labels = collect($channel_month_keys)
                ->map(function ($month_key) use ($thai_month_abbr) {
                    return $thai_month_abbr[(int) substr($month_key, 5, 2)] ?? $month_key;
                })
                ->values();

            $channel_others_color = '#cbd5e1';
            $channel_no_promotion_color = '#94a3b8';
            $channel_series_top_n = 7;

            // แมปสีของแต่ละชื่อ ใช้ร่วมกันทั้งกราฟและลิสต์ ให้สีตรงกันเป๊ะ
            // - โปรจริง: ผูกสีกับ "ชื่อ" แบบ deterministic (hash) → โปรเดียวกันได้สีเดิมเสมอ ไม่ขึ้นกับอันดับ
            // - No Promotion: สีเทาเข้ม / เกิน Top N: สีเทาอ่อน (รวมเป็น "อื่นๆ")
            $channel_build_color_map = function (array $ranking) use ($channel_bar_palette, $channel_series_top_n, $channel_others_color, $channel_no_promotion_color) {
                $palette_count = count($channel_bar_palette);
                $map = [];
                $used = [];
                $rank_index = 0;
                foreach (array_keys($ranking) as $name) {
                    $is_no_promotion = str_contains(strtolower($name), 'no promotion');
                    if ($rank_index >= $channel_series_top_n) {
                        $map[$name] = $channel_others_color;
                    } elseif ($is_no_promotion) {
                        $map[$name] = $channel_no_promotion_color;
                    } else {
                        // hash ชื่อให้ได้สีที่ต้องการ (คงที่ต่อชื่อ) จัดการค่าติดลบบน PHP 32-bit ด้วย
                        $palette_index = ((crc32($name) % $palette_count) + $palette_count) % $palette_count;
                        // ถ้าสีชนกับตัวที่จองไปแล้วในใบนี้ เลื่อนไปสีถัดไป (กันสีซ้ำในรายงานเดียว)
                        $probe = 0;
                        while (isset($used[$palette_index]) && $probe < $palette_count) {
                            $palette_index = ($palette_index + 1) % $palette_count;
                            $probe++;
                        }
                        $used[$palette_index] = true;
                        $map[$name] = $channel_bar_palette[$palette_index];
                    }
                    $rank_index++;
                }
                return $map;
            };
            $channel_promotion_color_map = $channel_build_color_map($channel_promotion_ranking);
            $channel_source_color_map = $channel_build_color_map($channel_source_ranking);

            // สร้าง grouped series: แสดงทีละตัวตามอันดับ (Top N) เกินกว่านั้นยุบเป็น "อื่นๆ (รวม)"
            $build_channel_stacked_series = function (array $ranking, array $month_name_students, array $month_keys, array $color_map) use ($channel_series_top_n, $channel_others_color) {
                $top_names = array_slice(array_keys($ranking), 0, $channel_series_top_n);
                $top_lookup = array_flip($top_names);
                $series = [];

                foreach ($top_names as $name) {
                    $series[] = [
                        'label' => $name,
                        'color' => $color_map[$name] ?? $channel_others_color,
                        'data' => array_map(function ($month_key) use ($month_name_students, $name) {
                            return count($month_name_students[$month_key][$name] ?? []);
                        }, $month_keys),
                    ];
                }

                if (count($ranking) > count($top_names)) {
                    $others = array_map(function ($month_key) use ($month_name_students, $top_lookup) {
                        $sum = 0;
                        foreach ($month_name_students[$month_key] ?? [] as $name => $student_ids) {
                            if (!isset($top_lookup[$name])) {
                                $sum += count($student_ids);
                            }
                        }
                        return $sum;
                    }, $month_keys);

                    $series[] = ['label' => 'อื่นๆ (รวม)', 'color' => $channel_others_color, 'data' => $others];
                }

                return $series;
            };

            $channel_promotion_series = $build_channel_stacked_series($channel_promotion_ranking, $channel_month_promotion_students, $channel_month_keys, $channel_promotion_color_map);
            $channel_source_series = $build_channel_stacked_series($channel_source_ranking, $channel_month_source_students, $channel_month_keys, $channel_source_color_map);
        @endphp

        <div class="page">
            <div class="report-header">
                <div class="logo-area">IB</div>
                <div class="header-text">
                    <h1>กราฟ Promotion / Source</h1>
                    <p>Channel Overview</p>
                </div>
                <div class="header-right">
                    <div class="year-badge">{{ $channel_period_label }}</div>
                    <div class="print-date">พิมพ์เมื่อ {{ Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="section-title">
                <span class="icon">📊</span> ภาพรวม Promotion / Source {{ $channel_demo_month === 'all' ? 'รายเดือน ' : '' }}{{ $channel_period_label }}
            </div>

            @if ($channel_student_total === 0)
                <div class="empty-state">ไม่มีข้อมูลนักเรียนใน{{ $channel_period_label }}</div>
            @else
                <div class="print-chart-layout">
                    <div class="print-chart-panel">
                        <div class="print-chart-panel-head">
                            <div class="print-chart-panel-title">Promotion</div>
                            <div class="print-chart-panel-note">นับนักเรียนไม่ซ้ำจากวันที่สมัครใน receipt {{ $channel_period_label }}</div>
                        </div>
                        <div class="print-chart-panel-body">
                            <div class="channel-bar-summary">
                                <span class="channel-bar-pct">{{ $channel_promotion_percent }}%</span>
                                <span class="channel-bar-pct-label">มี Promotion · {{ $channel_promotion_student_count }}/{{ $channel_student_total }} คน</span>
                            </div>
                            <div class="channel-bar-canvas">
                                <canvas id="promotionBarChart"></canvas>
                            </div>

                            <div class="channel-rank-title">อันดับ Promotion ที่ใช้มากที่สุด</div>
                            <div class="channel-rank-note">* นับตามรายการโปร — 1 คนใช้ได้หลายโปร ผลรวมจึงมากกว่าจำนวนคนได้</div>
                            @forelse ($channel_promotion_ranking as $promotion_name => $promotion_count)
                                @php
                                    $is_no_promotion = str_contains(strtolower($promotion_name), 'no promotion');
                                    $promotion_bar_width = $channel_promotion_rank_max > 0 ? round(($promotion_count / $channel_promotion_rank_max) * 100) : 0;
                                    $promotion_bar_color = $channel_promotion_color_map[$promotion_name] ?? '#cbd5e1';
                                @endphp
                                <div class="channel-rank-item {{ $is_no_promotion ? 'is-muted' : '' }}">
                                    <div class="channel-rank-main">
                                        <div class="channel-rank-head">
                                            <span class="channel-rank-no">{{ $loop->iteration }}.</span>
                                            <span class="channel-rank-name">{{ $promotion_name }}</span>
                                        </div>
                                        <div class="channel-rank-track">
                                            <span class="channel-rank-fill" style="width: {{ $promotion_bar_width }}%; background: {{ $promotion_bar_color }};"></span>
                                        </div>
                                    </div>
                                    <div class="channel-rank-val">{{ $promotion_count }}</div>
                                </div>
                            @empty
                                <div class="empty-state">ไม่มี Promotion ใน{{ $channel_period_label }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="print-chart-panel">
                        <div class="print-chart-panel-head">
                            <div class="print-chart-panel-title">Source</div>
                            <div class="print-chart-panel-note">นับนักเรียนไม่ซ้ำจากวันที่สมัครใน receipt {{ $channel_period_label }}</div>
                        </div>
                        <div class="print-chart-panel-body">
                            <div class="channel-bar-summary">
                                <span class="channel-bar-pct">{{ $channel_source_percent }}%</span>
                                <span class="channel-bar-pct-label">มี Source · {{ $channel_source_student_count }}/{{ $channel_student_total }} คน</span>
                            </div>
                            <div class="channel-bar-canvas">
                                <canvas id="sourceBarChart"></canvas>
                            </div>

                            <div class="channel-rank-title">อันดับ Source ที่มามากที่สุด</div>
                            <div class="channel-rank-note">* นับตามรายการ source — 1 คนมีได้หลาย source ผลรวมจึงมากกว่าจำนวนคนได้</div>
                            @forelse ($channel_source_ranking as $source_name => $source_count)
                                @php
                                    $source_bar_width = $channel_source_rank_max > 0 ? round(($source_count / $channel_source_rank_max) * 100) : 0;
                                    $source_bar_color = $channel_source_color_map[$source_name] ?? '#cbd5e1';
                                @endphp
                                <div class="channel-rank-item">
                                    <div class="channel-rank-main">
                                        <div class="channel-rank-head">
                                            <span class="channel-rank-no">{{ $loop->iteration }}.</span>
                                            <span class="channel-rank-name">{{ $source_name }}</span>
                                        </div>
                                        <div class="channel-rank-track">
                                            <span class="channel-rank-fill" style="width: {{ $source_bar_width }}%; background: {{ $source_bar_color }};"></span>
                                        </div>
                                    </div>
                                    <div class="channel-rank-val">{{ $source_count }}</div>
                                </div>
                            @empty
                                <div class="empty-state">ไม่มี Source ใน{{ $channel_period_label }}</div>
                            @endforelse
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
            const channel_month_labels = @json($channel_month_chart_labels);
            const promotion_series = @json($channel_promotion_series);
            const source_series = @json($channel_source_series);

            // วาดจำนวนคนไว้บนหัวแท่งแต่ละแท่ง (เฉพาะแท่งที่มีค่า)
            const channelGroupedValuePlugin = {
                id: 'channelGroupedValue',
                afterDatasetsDraw: function(chart) {
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.font = 'bold 8px sans-serif';
                    ctx.fillStyle = '#475569';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    chart.data.datasets.forEach(function(dataset, dataset_index) {
                        const meta = chart.getDatasetMeta(dataset_index);
                        if (meta.hidden) {
                            return;
                        }
                        meta.data.forEach(function(bar, index) {
                            const value = dataset.data[index] || 0;
                            if (value > 0) {
                                ctx.fillText(value, bar.x, bar.y - 2);
                            }
                        });
                    });
                    ctx.restore();
                }
            };

            function CreateChannelGroupedBar(canvas_id, labels, series) {
                const canvas = document.getElementById(canvas_id);
                if (!canvas || series.length === 0) {
                    return;
                }

                const datasets = series.map(function(item) {
                    return {
                        label: item.label,
                        data: item.data,
                        // ใช้สีจาก color map (ฝั่ง PHP) ให้ตรงกับลิสต์อันดับด้านล่างเป๊ะ
                        backgroundColor: item.color,
                        borderWidth: 0,
                        borderRadius: 3,
                    };
                });

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        layout: {
                            padding: {
                                top: 16
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 10,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' คน';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 9
                                    },
                                    autoSkip: false,
                                    maxRotation: 0,
                                    minRotation: 0
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font: {
                                        size: 9
                                    }
                                },
                                grid: {
                                    color: '#eef2f7'
                                }
                            }
                        }
                    },
                    plugins: [channelGroupedValuePlugin]
                });
            }

            CreateChannelGroupedBar('promotionBarChart', channel_month_labels, promotion_series);
            CreateChannelGroupedBar('sourceBarChart', channel_month_labels, source_series);
        @endif
    </script>
</body>

</html>
