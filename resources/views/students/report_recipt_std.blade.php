@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="../assets/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
    <link href="/assets/plugins/switchery/dist/switchery.min.css" rel="stylesheet" />
    <style>
        .screen-auto {
            max-width: 100%;
            height: auto;
        }

        /* Timeline Chart Styles */
        .timeline-container {
            overflow-x: auto;
            padding: 20px 0;
        }

        .timeline-chart {
            min-width: 100%;
        }

        .timeline-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            min-height: 40px;
        }

        .timeline-label {
            width: 180px;
            min-width: 180px;
            padding-right: 15px;
            font-size: 13px;
            text-align: right;
        }

        .timeline-label .student-code {
            color: #666;
            font-size: 11px;
        }

        .timeline-bars {
            flex: 1;
            display: flex;
            position: relative;
            min-height: 30px;
            background: #f8f9fa;
            border-radius: 4px;
            flex-direction: column;
        }

        .timeline-bars-inner {
            position: relative;
            height: 28px;
            margin-bottom: 2px;
        }

        .timeline-bars-inner:last-child {
            margin-bottom: 0;
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
            height: 22px;
            top: 3px;
            border-radius: 4px;
            opacity: 0.85;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            z-index: 1;
        }

        .timeline-bar:hover {
            opacity: 1;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .timeline-header {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }

        .timeline-header-label {
            width: 180px;
            min-width: 180px;
            font-weight: bold;
        }

        .timeline-header-months {
            flex: 1;
            display: flex;
        }

        .timeline-header-month {
            flex: 1;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #495057;
        }

        .timeline-actions {
            width: 60px;
            min-width: 60px;
            text-align: center;
        }

        /* Group Colors */
        .group-M {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .group-E {
            background: linear-gradient(135deg, #2196F3, #1976D2);
        }

        .group-C,
        .group-CHN {
            background: linear-gradient(135deg, #FF9800, #F57C00);
        }

        .group-S,
        .group-Sci {
            background: linear-gradient(135deg, #9C27B0, #7B1FA2);
        }

        .group-T {
            background: linear-gradient(135deg, #00BCD4, #0097A7);
        }

        .group-J {
            background: linear-gradient(135deg, #E91E63, #C2185B);
        }

        .group-K {
            background: linear-gradient(135deg, #795548, #5D4037);
        }

        .group-default {
            background: linear-gradient(135deg, #607D8B, #455A64);
        }

        /* Legend */
        .timeline-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .legend-color {
            width: 24px;
            height: 16px;
            border-radius: 3px;
        }

        /* Search box for timeline */
        .timeline-search {
            margin-bottom: 15px;
        }

        /* ====== PRINT STYLES ====== */
        @media print {

            /* Hide all UI except selected print sections */
            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            #print-area {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }

            /* A4 sizing handled by JS before print */
        }

        /* Print section wrappers */
        .print-section {
            display: block;
        }

        .print-hidden {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">

        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-users icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>Students
                        <div class="page-title-subheading">Infinite Brain Student Data</div>
                    </div>
                </div>
                <div class="page-title-actions no-print">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#printModal">
                        <i class="fa fa-print"></i> พิมพ์รายงาน
                    </button>
                </div>
            </div>
        </div>

        {{-- ==================== PRINT MODAL ==================== --}}
        <div class="modal fade no-print" id="printModal" tabindex="-1" aria-labelledby="printModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printModalLabel"><i class="fa fa-print"></i> เลือกส่วนที่ต้องการพิมพ์
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="fw-bold mb-2 d-block">ส่วนของรายงาน</label>
                            {{-- <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chk-monthly-table"
                                    value="section-monthly-table" checked>
                                <label class="form-check-label" for="chk-monthly-table">ตารางค่าสมัครเรียนรายเดือน</label>
                            </div> --}}
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chk-all-month-chart"
                                    value="section-all-month-chart" checked>
                                <label class="form-check-label" for="chk-all-month-chart">กราฟสรุปรายเดือน (All Month
                                    Report)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chk-timeline" value="section-timeline"
                                    checked>
                                <label class="form-check-label" for="chk-timeline">กราฟ Timeline นักเรียนรายปี</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chk-summary-table"
                                    value="section-summary-table" checked>
                                <label class="form-check-label"
                                    for="chk-summary-table">ตารางสรุปจำนวนนักเรียนแต่ละวิชาต่อเดือน</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chk-demographics"
                                    value="section-demographics" checked>
                                <label class="form-check-label" for="chk-demographics">ข้อมูลประชากรนักเรียน
                                    (อายุ/เพศ/ชั้นเรียน)</label>
                            </div>
                            {{-- <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chk-paper-table"
                                    value="section-paper-table" checked>
                                <label class="form-check-label" for="chk-paper-table">บันทึกใบปะหน้า</label>
                            </div> --}}
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="fw-bold mb-2 d-block">การวางกระดาษ A4</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="orientation" id="orient-portrait"
                                        value="portrait" checked>
                                    <label class="form-check-label" for="orient-portrait">
                                        <i class="fa fa-file"></i> แนวตั้ง (Portrait)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="orientation" id="orient-landscape"
                                        value="landscape">
                                    <label class="form-check-label" for="orient-landscape">
                                        <i class="fa fa-file-text-o"></i> แนวนอน (Landscape)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-primary" id="doPrint">
                            <i class="fa fa-print"></i> พิมพ์
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ==================== END PRINT MODAL ==================== --}}

        {{-- <div class="main-card mb-3 card"> --}}
        {{-- <div class="card-body"> --}}
        <div class="row mb-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header-tab card-header-tab-animation card-header">
                        <div class="card-header-title">
                            <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                            All Month Report
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            {{--  --}}
                            <canvas id="barChart" {{-- width="800" height="200" --}}></canvas>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header-tab card-header-tab-animation card-header">
                        <div class="card-header-title">
                            <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                            All Month Report
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <canvas id="lineChart" {{-- width="800" height="200" --}}></canvas>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- </div> --}}

        {{-- </div> --}}


        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">


                <form action="{{ route('reportRecieptStdSearch') }}" method="GET">
                    @csrf
                    <div class="row mb-5">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="col-lg-2">
                            <div class="row">
                                <label for="year" class="col-lg-2">ปี : </label>
                                <div class="col-lg-9">
                                    <select name="year" id="selectMonth" class="form-select">
                                        {{-- <option value="">เลือกปี</option> --}}
                                        @php
                                            $currentYear = date('Y') + 1;
                                            for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                                $selected = isset($year) && $year == $i ? 'selected' : '';
                                                echo "<option value='$i' $selected>$i</option>";
                                            }
                                        @endphp
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <button class="btn btn-primary" type="submit">ค้นหา</button>
                        </div>

                    </div>
                </form>

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a href="#default-tab-1" data-bs-toggle="tab" class="nav-link">
                            {{-- <span class="d-sm-none">Tab 1</span> --}}
                            <span class="d-sm-block d-none">ตรวจสอบรายเดือน</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#default-tab-2" data-bs-toggle="tab" class="nav-link active">
                            {{-- <span class="d-sm-none">Tab 1</span> --}}
                            <span class="d-sm-block d-none">ตรวจสอบรายปี</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#default-tab-3" data-bs-toggle="tab" class="nav-link">
                            {{-- <span class="d-sm-none">Tab 2</span> --}}
                            <span class="d-sm-block d-none">บันทึกใบปะหน้า</span>
                        </a>
                    </li>

                </ul>
                <div class="tab-content panel m-0 rounded-0 p-3">
                    <!-- BEGIN tab-pane -->
                    <div class="tab-pane fade print-section" id="default-tab-1">
                        <div id="section-monthly-table">

                            <h3 class="mt-10px"><i class="fa fa-cog"></i> ตารางค่าสมัครเรียนเดือน
                                {{ thaidate('F', $month) }}
                                {{ $year }}</h3>
                            <table style="width: 100%;" id="Listview"
                                class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">

                                <thead>
                                    <tr>
                                        <th width='1%' class="text-center">no.</th>
                                        <th width='1%'>Code</th>
                                        <th>Student Nickname</th>
                                        <th>Submission Date</th>

                                        <th>Period</th>
                                        <th>Reciept Number</th>

                                        <th>Group</th>
                                        <th>Stutus</th>
                                        <th>Tpye</th>
                                        <th>total</th>
                                        <th>Check</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        use App\Models\Statuslog;

                                        $sum = 0;
                                        $sum1 = 0;
                                    @endphp
                                    @foreach ($reportMonth_schoolPrice as $studentId => $groupedData)
                                        @foreach ($groupedData as $key => $data)
                                            @php
                                                $class = '';

                                                $cMonth = date('m');
                                                $cYear = date('Y');
                                                $status = Statuslog::where('student_id', $data->std->id)
                                                    ->where('group', $data->group)
                                                    ->whereMonth('status_date', $cMonth)
                                                    ->whereYear('status_date', $cYear)
                                                    ->get();

                                                if ($status->last()->status === 'None Receipt') {
                                                    $class = 'text-info';
                                                } elseif ($status->last()->status === 'drop') {
                                                    $class = 'text-black';
                                                } elseif ($status->last()->status === 'waitting . . .') {
                                                    $class = 'text-primary';
                                                } elseif ($status->last()->status === 'Termination of study') {
                                                    $class = 'text-warning';
                                                } elseif ($status->last()->status === 'New Student') {
                                                    $class = 'text-red';
                                                } else {
                                                    $class = 'text-blue';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $key + 1 }}</td>
                                                <td class="text-center">{{ $data->std->code ?? 'Null' }}</td>
                                                <td class="{{ $class }}">{{ $data->std->nick_name ?? 'Null' }}</td>
                                                <td>{{ $data->rec_school->submission_date }}</td>
                                                <td>{{ $data->period }}</td>

                                                <td>{{ $data->rec_school->receipt_number }}</td>

                                                <td>{{ $data->group }}</td>


                                                <td class="{{ $class }}">
                                                    {{-- @if ($data->std->status_log && $data->std->status_log->count() > 0 && $status->last()->group == $data->group) --}}
                                                    @if (
                                                        $data->std &&
                                                            $data->std->status_log &&
                                                            $data->std->status_log->count() > 0 &&
                                                            $status->last() &&
                                                            $status->last()->group == $data->group)
                                                        {{ $status->last()->status ?? 'Null' }}
                                                    @else
                                                        null
                                                    @endif
                                                </td>
                                                <td>{{ $data->course_type }}</td>
                                                {{-- <td class="text-end">{{ number_format($data->total) }} บาท</td> --}}
                                                <td class="text-end">{{ number_format($data->total) }} บาท</td>
                                                {{-- <td class="text-end"><input type="checkbox" id="switchery-default" checked /></td> --}}
                                                <td class="text-center">
                                                    {{-- <input type="checkbox" class="switchery-small" id="switchery-default-{{ $loop->index }}" checked /> --}}
                                                    <div class="form-check form-switch form-switch-sm">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="flexSwitchCheckChecked-{{ $data->rec_school->receipt_number }}">
                                                    </div>
                                                </td>

                                            </tr>
                                            @php
                                                $sum += $data->total;
                                            @endphp
                                        @endforeach
                                    @endforeach

                                </tbody>
                                <tbody>
                                    <tr>
                                        <td class="text-center">#</td>
                                        {{-- <td class="text-center" colspan="2"></td> <!-- Merged cell --> --}}
                                        <td class="text-center" hidden></td> <!-- Merged cell -->
                                        <td class="text-center" hidden></td> <!-- Merged cell -->
                                        <td class="text-center" hidden></td> <!-- Merged cell -->
                                        <td class="text-center" hidden></td> <!-- Merged cell -->
                                        <td class="text-center" hidden></td> <!-- Merged cell -->
                                        <td class="text-center" hidden></td> <!-- Merged cell -->
                                        <td class="text-center" colspan="8">Sum Total</td> <!-- Merged cell -->
                                        <td class="text-end">{{ number_format($sum) }} บาท</td>
                                        <td></td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>{{-- end #section-monthly-table --}}
                    </div>{{-- end tab-pane #default-tab-1 --}}
                    <div class="tab-pane fade active show print-section" id="default-tab-2">
                        <div id="section-timeline">

                            <h3 class="mt-10px"><i class="fa fa-cog"></i> ตารางค่าสมัครเรียนปี {{ $year }}</h3>

                            <!-- Legend -->
                            <div class="timeline-legend">
                                <span class="fw-bold me-3">กลุ่มวิชา:</span>
                                <div class="legend-item">
                                    <div class="legend-color group-M"></div>
                                    <span>M - คณิตศาสตร์</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color group-E"></div>
                                    <span>E - ภาษาอังกฤษ</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color group-C"></div>
                                    <span>C - ภาษาจีน</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color group-S"></div>
                                    <span>S - วิทยาศาสตร์</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color group-T"></div>
                                    <span>T - ภาษาไทย</span>
                                </div>
                                {{-- <div class="legend-item">
                                <div class="legend-color group-J"></div>
                                <span>J - ภาษาญี่ปุ่น</span>
                            </div> --}}
                                {{-- <div class="legend-item">
                                <div class="legend-color group-K"></div>
                                <span>K - ภาษาเกาหลี</span>
                            </div> --}}
                            </div>

                            <!-- Search -->
                            <div class="timeline-search">
                                <input type="text" id="timelineSearch" class="form-control"
                                    placeholder="🔍 ค้นหาชื่อนักเรียน...">
                            </div>

                            <!-- Timeline Chart -->
                            <div class="timeline-container">
                                <div class="timeline-chart">
                                    <!-- Header -->
                                    <div class="timeline-header">
                                        <div class="timeline-header-label">นักเรียน</div>
                                        <div class="timeline-header-months">
                                            @for ($i = 1; $i <= 12; $i++)
                                                <div class="timeline-header-month">
                                                    {{ date('M', mktime(0, 0, 0, $i, 1)) }}
                                                </div>
                                            @endfor
                                        </div>
                                        <div class="timeline-actions">View</div>
                                    </div>

                                    <!-- Data Rows -->
                                    @php
                                        use Carbon\Carbon;
                                        // ลำดับการเรียงวิชา
                                        $subjectOrder = [
                                            'M' => 1,
                                            'E' => 2,
                                            'C' => 3,
                                            'S' => 4,
                                            'T' => 5,
                                            'J' => 6,
                                            'K' => 7,
                                        ];
                                    @endphp
                                    @foreach ($reportYear_schoolPrice as $studentId => $groupedData)
                                        @php
                                            $studentCode = $groupedData->first()->std->code ?? 'N/A';
                                            $studentName = $groupedData->first()->std->nick_name ?? 'Unknown';

                                            // สร้างข้อมูล timeline bars แยกตามกลุ่มวิชา
                                            $timelineBars = [];
                                            foreach ($groupedData as $data1) {
                                                $period = explode(' - ', $data1->period);

                                                if (count($period) == 1) {
                                                    $periods = explode('-', $data1->period);
                                                    $startMonth = (int) $periods[1];
                                                    $endMonth = count($periods) > 2 ? (int) $periods[3] : $startMonth;
                                                    $startYear = (int) $periods[0];
                                                    $endYear = count($periods) > 2 ? (int) $periods[2] : $startYear;
                                                } else {
                                                    $startDate = Carbon::parse($period[0]);
                                                    $endDate = Carbon::parse($period[1]);
                                                    $startMonth = $startDate->month;
                                                    $endMonth = $endDate->month;
                                                    $startYear = $startDate->year;
                                                    $endYear = $endDate->year;
                                                }

                                                $group = $data1->group;
                                                $groupMark = $group === 'CHN' ? 'C' : ($group === 'Sci' ? 'S' : $group);

                                                // คำนวณ position และ width (รองรับข้อมูลข้ามปี)
                                                $yearInt = (int) $year;
                                                if (
                                                    ($startYear <= $yearInt && $endYear >= $yearInt) ||
                                                    $startYear == $yearInt ||
                                                    $endYear == $yearInt
                                                ) {
                                                    // คำนวณ start และ end สำหรับปีที่เลือก
                                                    if ($startYear < $yearInt) {
                                                        $barStart = 1; // เริ่มตั้งแต่เดือน 1
                                                    } else {
                                                        $barStart = $startMonth;
                                                    }

                                                    if ($endYear > $yearInt) {
                                                        $barEnd = 12; // สิ้นสุดเดือน 12
                                                    } else {
                                                        $barEnd = $endMonth;
                                                    }

                                                    $timelineBars[$groupMark][] = [
                                                        'start' => $barStart,
                                                        'end' => $barEnd,
                                                        'group' => $groupMark,
                                                        'groupFull' => $group,
                                                        'period' => $data1->period,
                                                    ];
                                                }
                                            }

                                            // นับจำนวนแถวที่ต้องแสดง และเรียงตามลำดับวิชา
                                            uksort($timelineBars, function ($a, $b) use ($subjectOrder) {
                                                $orderA = $subjectOrder[$a] ?? 99;
                                                $orderB = $subjectOrder[$b] ?? 99;
                                                return $orderA - $orderB;
                                            });
                                            $totalRows = count($timelineBars);
                                        @endphp
                                        <div class="timeline-row" data-student="{{ strtolower($studentName) }}">
                                            <div class="timeline-label">
                                                <div>{{ $studentName }}</div>
                                                <div class="student-code">{{ $studentCode }}</div>
                                            </div>
                                            <div class="timeline-bars"
                                                style="min-height: {{ max(30, $totalRows * 30) }}px;">
                                                @foreach ($timelineBars as $groupMark => $bars)
                                                    <div class="timeline-bars-inner">
                                                        <!-- Month Grid -->
                                                        <div class="timeline-month-grid">
                                                            @for ($month = 1; $month <= 12; $month++)
                                                                <div class="timeline-month"
                                                                    data-month="{{ $month }}">
                                                                </div>
                                                            @endfor
                                                        </div>
                                                        <!-- Bars for this group -->
                                                        @foreach ($bars as $bar)
                                                            @php
                                                                $left = (($bar['start'] - 1) / 12) * 100;
                                                                $width = (($bar['end'] - $bar['start'] + 1) / 12) * 100;
                                                                $groupClass = in_array($bar['group'], [
                                                                    'M',
                                                                    'E',
                                                                    'C',
                                                                    'S',
                                                                    'T',
                                                                    'J',
                                                                    'K',
                                                                ])
                                                                    ? 'group-' . $bar['group']
                                                                    : 'group-default';
                                                            @endphp
                                                            <div class="timeline-bar {{ $groupClass }}"
                                                                style="left: {{ $left }}%; width: {{ $width }}%;"
                                                                title="{{ $bar['groupFull'] }}: {{ $bar['period'] }}">
                                                                {{ $bar['group'] }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                                @if (count($timelineBars) == 0)
                                                    <div class="timeline-bars-inner">
                                                        <div class="timeline-month-grid">
                                                            @for ($month = 1; $month <= 12; $month++)
                                                                <div class="timeline-month"
                                                                    data-month="{{ $month }}">
                                                                </div>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="timeline-actions">
                                                <a href="javascript:;" class="btn btn-xs btn-primary"
                                                    onclick="formModal('{{ route('reportRecieptStdShow', ['id' => $studentId]) }}')"
                                                    data-bs-toggle="tooltip" data-bs-trigger="hover"
                                                    data-bs-container="body" data-bs-title="View Detail">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- สรุปจำนวน -->
                            <div class="mt-3 text-muted">
                                <small>แสดง <span id="visibleCount">{{ count($reportYear_schoolPrice) }}</span> จาก
                                    {{ count($reportYear_schoolPrice) }} รายการ</small>
                            </div>

                            <!-- ตารางสรุปจำนวนนักเรียนแต่ละวิชาต่อเดือน -->
                            @php
                                // สร้างข้อมูลสรุปจำนวนนักเรียนแต่ละวิชาในแต่ละเดือน
                                $subjectMonthly = [];
                                $allGroups = [];
                                $yearInt = (int) $year;

                                foreach ($reportYear_schoolPrice as $studentId => $groupedData) {
                                    foreach ($groupedData as $data1) {
                                        $period = explode(' - ', $data1->period);

                                        if (count($period) == 1) {
                                            $periods = explode('-', $data1->period);
                                            $startMonth = (int) $periods[1];
                                            $endMonth = count($periods) > 2 ? (int) $periods[3] : $startMonth;
                                            $startYear = (int) $periods[0];
                                            $endYear = count($periods) > 2 ? (int) $periods[2] : $startYear;
                                        } else {
                                            $startDate = Carbon::parse($period[0]);
                                            $endDate = Carbon::parse($period[1]);
                                            $startMonth = $startDate->month;
                                            $endMonth = $endDate->month;
                                            $startYear = $startDate->year;
                                            $endYear = $endDate->year;
                                        }

                                        $group = $data1->group;
                                        if (!in_array($group, $allGroups)) {
                                            $allGroups[] = $group;
                                        }

                                        // นับนักเรียนในแต่ละเดือนของปีที่เลือก (รองรับข้ามปี)
                                        if (
                                            ($startYear <= $yearInt && $endYear >= $yearInt) ||
                                            $startYear == $yearInt ||
                                            $endYear == $yearInt
                                        ) {
                                            // คำนวณ start และ end สำหรับปีที่เลือก
                                            if ($startYear < $yearInt) {
                                                $barStart = 1;
                                            } else {
                                                $barStart = $startMonth;
                                            }

                                            if ($endYear > $yearInt) {
                                                $barEnd = 12;
                                            } else {
                                                $barEnd = $endMonth;
                                            }

                                            for ($m = $barStart; $m <= $barEnd; $m++) {
                                                if (!isset($subjectMonthly[$group][$m])) {
                                                    $subjectMonthly[$group][$m] = [];
                                                }
                                                if (!in_array($studentId, $subjectMonthly[$group][$m])) {
                                                    $subjectMonthly[$group][$m][] = $studentId;
                                                }
                                            }
                                        }
                                    }
                                }

                                // เรียงลำดับกลุ่มวิชา
                                sort($allGroups);
                            @endphp
                            <div id="section-summary-table">
                                <h4 class="mt-5 mb-3"><i class="fa fa-table"></i> สรุปจำนวนนักเรียนแต่ละวิชาต่อเดือน ปี
                                    {{ $year }}</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center" style="width: 120px;">วิชา</th>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <th class="text-center">
                                                        {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</th>
                                                @endfor
                                                <th class="text-center bg-warning text-dark">รวม</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $monthTotals = array_fill(1, 12, 0);
                                            @endphp
                                            @foreach ($allGroups as $group)
                                                @php
                                                    $groupName = match ($group) {
                                                        'M' => 'M - คณิตศาสตร์',
                                                        'E' => 'E - ภาษาอังกฤษ',
                                                        'C', 'CHN' => 'C - ภาษาจีน',
                                                        'S', 'Sci' => 'S - วิทยาศาสตร์',
                                                        'T' => 'T - ภาษาไทย',
                                                        'J' => 'J - ภาษาญี่ปุ่น',
                                                        'K' => 'K - ภาษาเกาหลี',
                                                        default => $group,
                                                    };
                                                    $groupMark =
                                                        $group === 'CHN' ? 'C' : ($group === 'Sci' ? 'S' : $group);
                                                    $groupClass = in_array($groupMark, [
                                                        'M',
                                                        'E',
                                                        'C',
                                                        'S',
                                                        'T',
                                                        'J',
                                                        'K',
                                                    ])
                                                        ? 'group-' . $groupMark
                                                        : 'group-default';
                                                    $rowTotal = 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="badge {{ $groupClass }}"
                                                            style="font-size: 12px;">{{ $groupName }}</span>
                                                    </td>
                                                    @for ($m = 1; $m <= 12; $m++)
                                                        @php
                                                            $count = isset($subjectMonthly[$group][$m])
                                                                ? count($subjectMonthly[$group][$m])
                                                                : 0;
                                                            $rowTotal += $count;
                                                            $monthTotals[$m] += $count;
                                                        @endphp
                                                        <td
                                                            class="text-center {{ $count > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                                            {{ $count > 0 ? $count : '-' }}
                                                        </td>
                                                    @endfor
                                                    <td class="text-center bg-light fw-bold">{{ $rowTotal }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-secondary">
                                            <tr>
                                                <th class="text-center">รวมทั้งหมด</th>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <th class="text-center">
                                                        {{ $monthTotals[$m] > 0 ? $monthTotals[$m] : '-' }}
                                                    </th>
                                                @endfor
                                                <th class="text-center bg-warning text-dark">{{ array_sum($monthTotals) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fa fa-info-circle"></i> <strong>หมายเหตุ:</strong>
                                    ตัวเลขแสดงจำนวนนักเรียนที่มี
                                    period ครอบคลุมเดือนนั้นๆ ในปี {{ $year }}
                                </div>

                                {{-- ===== DEMOGRAPHICS SECTION ===== --}}
                                @php
                                    // Pre-calculate unique students per group AND month for demographics
                                    $demoGroups = ['M', 'E', 'C', 'CHN', 'S', 'Sci', 'T', 'J', 'K'];
                                    $monthlyDemoData = [];
                                    $yearlyDemoData = []; // Unique students per year per group

                                    // Initialize for all 12 months
                                    for ($m = 1; $m <= 12; $m++) {
                                        $monthlyDemoData[$m] = [];
                                    }

                                    foreach ($reportYear_schoolPrice as $studentId => $groupedData) {
                                        foreach ($groupedData as $data1) {
                                            $group = $data1->group;
                                            $std = $data1->std;
                                            if (!$std) {
                                                continue;
                                            }

                                            $age = null;
                                            if ($std->birth_date) {
                                                try {
                                                    $age = Carbon::parse($std->birth_date)->age;
                                                } catch (\Exception $e) {
                                                }
                                            }

                                            // Yearly - unique per student-group
                                            if (!isset($yearlyDemoData[$group][$studentId])) {
                                                $yearlyDemoData[$group][$studentId] = [
                                                    'age' => $age,
                                                    'sex' => $std->sex ?? null,
                                                    'grade' => $std->grade ?? null,
                                                ];
                                            }

                                            // Monthly - period based
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
                                                    if (!isset($monthlyDemoData[$m][$group][$studentId])) {
                                                        $monthlyDemoData[$m][$group][$studentId] = [
                                                            'age' => $age,
                                                            'sex' => $std->sex ?? null,
                                                            'grade' => $std->grade ?? null,
                                                        ];
                                                    }
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

                                    $currentViewMonth = (int) date('m');
                                @endphp
                                <div id="section-demographics">
                                    <div class="row align-items-center mt-5 mb-3">
                                        <div class="col-md-7">
                                            <h4>
                                                <i class="fa fa-users"></i>
                                                ข้อมูลประชากรนักเรียนแต่ละวิชา ปี {{ $year }}
                                            </h4>
                                        </div>
                                        <div class="col-md-5 text-end no-print">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" id="btn-demo-all"
                                                    class="btn btn-outline-primary demo-month-btn active"
                                                    onclick="ShowDemoMonth('all')">
                                                    รายปี
                                                </button>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <button type="button" id="btn-demo-{{ $m }}"
                                                        class="btn btn-outline-primary demo-month-btn"
                                                        onclick="ShowDemoMonth({{ $m }})">
                                                        {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        .demo-card {
                                            border-radius: 12px;
                                            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
                                            overflow: hidden;
                                            margin-bottom: 24px;
                                        }

                                        .demo-card-header {
                                            padding: 14px 20px;
                                            color: #fff;
                                            font-weight: 700;
                                            font-size: 15px;
                                            display: flex;
                                            align-items: center;
                                            gap: 10px;
                                        }

                                        .demo-card-body {
                                            padding: 16px 20px;
                                            background: #fff;
                                        }

                                        .demo-section-title {
                                            font-size: 12px;
                                            font-weight: 700;
                                            text-transform: uppercase;
                                            letter-spacing: .5px;
                                            color: #6c757d;
                                            margin-bottom: 8px;
                                        }

                                        .demo-bar-wrap {
                                            margin-bottom: 6px;
                                        }

                                        .demo-bar-label {
                                            font-size: 13px;
                                            display: flex;
                                            justify-content: space-between;
                                            margin-bottom: 2px;
                                        }

                                        .demo-bar-bg {
                                            background: #f0f0f0;
                                            border-radius: 20px;
                                            height: 10px;
                                            overflow: hidden;
                                        }

                                        .demo-bar-fill {
                                            height: 100%;
                                            border-radius: 20px;
                                            transition: width .4s;
                                        }

                                        .demo-pill {
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 4px;
                                            background: #f0f0f0;
                                            border-radius: 20px;
                                            padding: 3px 10px;
                                            font-size: 12px;
                                            margin: 3px;
                                            font-weight: 600;
                                        }

                                        .demo-stat-box {
                                            text-align: center;
                                            padding: 10px 14px;
                                            background: #f8f9fa;
                                            border-radius: 10px;
                                        }

                                        .demo-stat-num {
                                            font-size: 26px;
                                            font-weight: 800;
                                            line-height: 1;
                                        }

                                        .demo-stat-label {
                                            font-size: 11px;
                                            color: #6c757d;
                                            margin-top: 2px;
                                        }
                                    </style>

                                    {{-- ANNUAL SUMMARY BLOCK --}}
                                    <div class="row demo-month-content" id="demo-month-block-all" style="display: flex;">
                                        <div class="col-12 mb-3 no-print">
                                            <span class="badge bg-primary p-2 px-3 fs-6">
                                                สรุปภาพรวมรายปี {{ $year }}
                                            </span>
                                        </div>
                                        @if (empty($yearlyDemoData))
                                            <div class="col-12 text-center py-5 text-muted">
                                                <i class="fa fa-info-circle fa-2x mb-2"></i>
                                                <p>ไม่มีข้อมูลประชากรในปีนี้</p>
                                            </div>
                                        @else
                                            @foreach ($yearlyDemoData as $group => $students)
                                                @php
                                                    $total = count($students);
                                                    $maleCount = 0;
                                                    $femaleCount = 0;
                                                    $ageRanges = [
                                                        '0-12' => 0,
                                                        '13-18' => 0,
                                                        '19+' => 0,
                                                        'ไม่ระบุ' => 0,
                                                    ];

                                                    foreach ($students as $s) {
                                                        $sex = strtolower($s['sex'] ?? '');
                                                        if ($sex == 'male' || $sex == 'ชาย') {
                                                            $maleCount++;
                                                        } elseif ($sex == 'female' || $sex == 'หญิง') {
                                                            $femaleCount++;
                                                        }

                                                        $a = $s['age'];
                                                        if ($a === null) {
                                                            $ageRanges['ไม่ระบุ']++;
                                                        } elseif ($a <= 12) {
                                                            $ageRanges['0-12']++;
                                                        } elseif ($a <= 18) {
                                                            $ageRanges['13-18']++;
                                                        } else {
                                                            $ageRanges['19+']++;
                                                        }
                                                    }

                                                    $color = $groupColors[$group] ?? '#607D8B';
                                                    $groupName = $groupNames[$group] ?? $group;

                                                    $showGrade = in_array($group, ['M', 'E']);
                                                    $grades = [];
                                                    if ($showGrade) {
                                                        foreach ($students as $student) {
                                                            $grade = trim($student['grade'] ?? 'ไม่ระบุ');
                                                            if ($grade === '' || $grade === null) {
                                                                $grade = 'ไม่ระบุ';
                                                            }
                                                            $grades[$grade] = ($grades[$grade] ?? 0) + 1;
                                                        }
                                                        arsort($grades);
                                                    }
                                                    $maxAge = max(array_values($ageRanges)) ?: 1;
                                                @endphp
                                                <div class="col-lg-6 col-md-6">
                                                    <div class="demo-card">
                                                        <div class="demo-card-header"
                                                            style="background: {{ $color }};">
                                                            <div class="icon-circle"
                                                                style="width: 24px; height: 24px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                                {{ substr($group, 0, 1) }}
                                                            </div>
                                                            {{ $groupName }}
                                                            <span
                                                                class="ms-auto badge bg-white text-dark">{{ $total }}
                                                                คน</span>
                                                        </div>
                                                        <div class="demo-card-body">
                                                            <div class="row text-center mb-3 g-2">
                                                                <div class="col-4">
                                                                    <div class="demo-stat-box">
                                                                        <div class="demo-stat-num"
                                                                            style="color: {{ $color }};">
                                                                            {{ $total }}</div>
                                                                        <div class="demo-stat-label">ทั้งหมด</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="demo-stat-box">
                                                                        <div class="demo-stat-num text-primary">
                                                                            {{ $maleCount }}</div>
                                                                        <div class="demo-stat-label">ชาย</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="demo-stat-box">
                                                                        <div class="demo-stat-num text-danger">
                                                                            {{ $femaleCount }}</div>
                                                                        <div class="demo-stat-label">หญิง</div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if ($total > 0)
                                                                @php
                                                                    $malePct = round(
                                                                        ($maleCount / ($total ?: 1)) * 100,
                                                                    );
                                                                    $femalePct = round(
                                                                        ($femaleCount / ($total ?: 1)) * 100,
                                                                    );
                                                                @endphp
                                                                <p class="demo-section-title">สัดส่วนเพศ</p>
                                                                <div class="demo-bar-wrap">
                                                                    <div class="demo-bar-label">
                                                                        <span>ชาย</span>
                                                                        <span>{{ $maleCount }} คน
                                                                            ({{ $malePct }}%)</span>
                                                                    </div>
                                                                    <div class="demo-bar-bg">
                                                                        <div class="demo-bar-fill"
                                                                            style="width: {{ $malePct }}%; background: #2196F3;">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="demo-bar-wrap mb-3">
                                                                    <div class="demo-bar-label">
                                                                        <span>หญิง</span>
                                                                        <span>{{ $femaleCount }} คน
                                                                            ({{ $femalePct }}%)</span>
                                                                    </div>
                                                                    <div class="demo-bar-bg">
                                                                        <div class="demo-bar-fill"
                                                                            style="width: {{ $femalePct }}%; background: #E91E63;">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <p class="demo-section-title">ช่วงอายุ</p>
                                                                @foreach ($ageRanges as $label => $count)
                                                                    @if ($count > 0)
                                                                        <div class="demo-bar-wrap">
                                                                            <div class="demo-bar-label">
                                                                                <span>{{ $label }}</span>
                                                                                <span>{{ $count }} คน</span>
                                                                            </div>
                                                                            <div class="demo-bar-bg">
                                                                                <div class="demo-bar-fill"
                                                                                    style="width: {{ round(($count / $maxAge) * 100) }}%; background: {{ $color }};">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach

                                                                @if ($showGrade && count($grades) > 0)
                                                                    <p class="demo-section-title mt-3">ระดับชั้น</p>
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        @foreach ($grades as $grade => $count)
                                                                            <span class="demo-pill"
                                                                                style="border: 1.5px solid {{ $color }}; color: {{ $color }};">
                                                                                {{ $grade }}
                                                                                <strong>{{ $count }}</strong>
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    @for ($m = 1; $m <= 12; $m++)
                                        <div class="row demo-month-content" id="demo-month-block-{{ $m }}"
                                            style="display: none;">
                                            <div class="col-12 mb-3 no-print">
                                                <span class="badge bg-info p-2 px-3 fs-6">
                                                    ประจำเดือน
                                                    {{ thaidate('F', Carbon::create($year, $m, 1)->format('F')) }}
                                                    {{ $year }}
                                                </span>
                                            </div>
                                            @if (empty($monthlyDemoData[$m]))
                                                <div class="col-12 text-center py-5 text-muted">
                                                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                                                    <p>ไม่มีข้อมูลประชากรในเดือนนี้</p>
                                                </div>
                                            @else
                                                @foreach ($monthlyDemoData[$m] as $group => $students)
                                                    @php
                                                        $total = count($students);
                                                        $maleCount = 0;
                                                        $femaleCount = 0;
                                                        $ageRanges = [
                                                            '0-12' => 0,
                                                            '13-18' => 0,
                                                            '19+' => 0,
                                                            'ไม่ระบุ' => 0,
                                                        ];

                                                        foreach ($students as $s) {
                                                            $sex = strtolower($s['sex'] ?? '');
                                                            if ($sex == 'male' || $sex == 'ชาย') {
                                                                $maleCount++;
                                                            } elseif ($sex == 'female' || $sex == 'หญิง') {
                                                                $femaleCount++;
                                                            }

                                                            $a = $s['age'];
                                                            if ($a === null) {
                                                                $ageRanges['ไม่ระบุ']++;
                                                            } elseif ($a <= 12) {
                                                                $ageRanges['0-12']++;
                                                            } elseif ($a <= 18) {
                                                                $ageRanges['13-18']++;
                                                            } else {
                                                                $ageRanges['19+']++;
                                                            }
                                                        }

                                                        $color = $groupColors[$group] ?? '#607D8B';
                                                        $groupName = $groupNames[$group] ?? $group;

                                                        $showGrade = in_array($group, ['M', 'E']);
                                                        $grades = [];
                                                        if ($showGrade) {
                                                            foreach ($students as $student) {
                                                                $grade = trim($student['grade'] ?? 'ไม่ระบุ');
                                                                if ($grade === '' || $grade === null) {
                                                                    $grade = 'ไม่ระบุ';
                                                                }
                                                                $grades[$grade] = ($grades[$grade] ?? 0) + 1;
                                                            }
                                                            arsort($grades);
                                                        }
                                                        $maxAge = max(array_values($ageRanges)) ?: 1;
                                                    @endphp
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="demo-card">
                                                            <div class="demo-card-header"
                                                                style="background: {{ $color }};">
                                                                <div class="icon-circle"
                                                                    style="width: 24px; height: 24px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                                    {{ substr($group, 0, 1) }}
                                                                </div>
                                                                {{ $groupName }}
                                                                <span
                                                                    class="ms-auto badge bg-white text-dark">{{ $total }}
                                                                    คน</span>
                                                            </div>
                                                            <div class="demo-card-body">
                                                                <div class="row text-center mb-3 g-2">
                                                                    <div class="col-4">
                                                                        <div class="demo-stat-box">
                                                                            <div class="demo-stat-num"
                                                                                style="color: {{ $color }};">
                                                                                {{ $total }}</div>
                                                                            <div class="demo-stat-label">ทั้งหมด</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="demo-stat-box">
                                                                            <div class="demo-stat-num text-primary">
                                                                                {{ $maleCount }}</div>
                                                                            <div class="demo-stat-label">ชาย</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="demo-stat-box">
                                                                            <div class="demo-stat-num text-danger">
                                                                                {{ $femaleCount }}</div>
                                                                            <div class="demo-stat-label">หญิง</div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                @if ($total > 0)
                                                                    @php
                                                                        $malePct = round(
                                                                            ($maleCount / ($total ?: 1)) * 100,
                                                                        );
                                                                        $femalePct = round(
                                                                            ($femaleCount / ($total ?: 1)) * 100,
                                                                        );
                                                                    @endphp
                                                                    <p class="demo-section-title">สัดส่วนเพศ</p>
                                                                    <div class="demo-bar-wrap">
                                                                        <div class="demo-bar-label">
                                                                            <span>ชาย</span>
                                                                            <span>{{ $maleCount }} คน
                                                                                ({{ $malePct }}%)</span>
                                                                        </div>
                                                                        <div class="demo-bar-bg">
                                                                            <div class="demo-bar-fill"
                                                                                style="width: {{ $malePct }}%; background: #2196F3;">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="demo-bar-wrap mb-3">
                                                                        <div class="demo-bar-label">
                                                                            <span>หญิง</span>
                                                                            <span>{{ $femaleCount }} คน
                                                                                ({{ $femalePct }}%)</span>
                                                                        </div>
                                                                        <div class="demo-bar-bg">
                                                                            <div class="demo-bar-fill"
                                                                                style="width: {{ $femalePct }}%; background: #E91E63;">
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <p class="demo-section-title">ช่วงอายุ</p>
                                                                    @foreach ($ageRanges as $label => $count)
                                                                        @if ($count > 0)
                                                                            <div class="demo-bar-wrap">
                                                                                <div class="demo-bar-label">
                                                                                    <span>{{ $label }}</span>
                                                                                    <span>{{ $count }} คน</span>
                                                                                </div>
                                                                                <div class="demo-bar-bg">
                                                                                    <div class="demo-bar-fill"
                                                                                        style="width: {{ round(($count / $maxAge) * 100) }}%; background: {{ $color }};">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach

                                                                    @if ($showGrade && count($grades) > 0)
                                                                        <p class="demo-section-title mt-3">ระดับชั้น</p>
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            @foreach ($grades as $grade => $count)
                                                                                <span class="demo-pill"
                                                                                    style="border: 1.5px solid {{ $color }}; color: {{ $color }};">
                                                                                    {{ $grade }}
                                                                                    <strong>{{ $count }}</strong>
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endfor
                                    {{-- ===== END DEMOGRAPHICS SECTION ===== --}}
                                </div> {{-- end #section-demographics --}}
                            </div> {{-- end tab-pane #default-tab-2 --}} <div class="tab-pane fade print-section" id="default-tab-3">
                                <div id="section-paper-table">
                                    <blockquote class="blockquote">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="mb-0">ตารางแสดงผลบันทึกใบปะหน้าประจำเดือน
                                                {{ thaidate('F', date('F')) }} {{ date('Y') }}</p>
                                        </div>
                                    </blockquote>
                                    <table style="width: 100%;" id="Listview2"
                                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action my-5">
                                        <thead>
                                            <tr>
                                                <th width='1%' class="text-center">#</th>
                                                <th width='1%' class="text-center">NO.</th>
                                                <th>Submission Date</th>
                                                <th>Student Nickname</th>
                                                <th>Reciept Number</th>
                                                <th>Period</th>
                                                <th>Group</th>
                                                <th>total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($paper as $key => $paper_rep)
                                                <tr>
                                                    <td class="text-center">{{ $key + 1 }}</td>
                                                    <td class="text-center">{{ $paper_rep->no }}</td>
                                                    <td>{{ $paper_rep->submission_date }}</td>
                                                    <td>{{ $paper_rep->student->nick_name }}</td>
                                                    <td>{{ $paper_rep->receipt_number }}</td>
                                                    <td>{{ $paper_rep->school_price->period ?? null }}</td>
                                                    <td>{{ $paper_rep->school_price->group ?? null }}</td>
                                                    <td class="text-end">
                                                        {{ number_format($paper_rep->school_price->total) ?? null }} บาท
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>{{-- end #section-paper-table --}}
                            </div>{{-- end tab-pane #default-tab-3 --}}
                        </div>{{-- end .tab-content --}}
                    </div>{{-- end .card-body --}}
                </div>{{-- end .card --}}
        </div>@endsection
        @section('script')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>{{-- <script src="https://d3js.org/d3.v7.min.js"></script> --}}
            <script src="../assets/plugins/d3/d3.min.js"></script>
            <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>{{-- <script src="../assets/plugins/nvd3/build/nv.d3.min.js"></script> --}} {{-- <script src="../assets/plugins/chart.js/dist/Chart.min.js"></script> --}}
            <script src="../assets/plugins/flot/source/jquery.canvaswrapper.js"></script>
            <script src="../assets/plugins/flot/source/jquery.colorhelpers.js"></script>
            <script src="../assets/plugins/flot/source/jquery.flot.js"></script>
            <script src="../assets/plugins/flot/source/jquery.flot.saturated.js"></script>
            <script src="../assets/plugins/flot/source/jquery.flot.browser.js"></script>
            <script src="../assets/plugins/flot/source/jquery.flot.drawSeries.js"></script>
            <script src="../assets/plugins/flot/source/jquery.flot.uiConstants.js"></script>
            <script src="/assets/plugins/switchery/dist/switchery.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
            <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>{{-- <script>
    {{-- <script>
        var checkboxes = document.querySelectorAll('input[id^="switchery-default-"]');
        checkboxes.forEach(function(checkbox) {
            var switchery = new Switchery(checkbox, {
                color: '#00acac'
            });
        });
    </script> --}}
            <script>
                $(document).ready(function() {
                    var selectMonth1 = document.getElementById('selectMonth1');

                    if (selectMonth1) {
                        selectMonth1.disabled = true;
                    }
                    var table1 = $('#Listview').DataTable({
                        lengthMenu: [-1],
                    });

                    var table2 = $('#Listview2').DataTable({
                        lengthMenu: [-1],
                    });

                    // Timeline Search
                    $('#timelineSearch').on('keyup', function() {
                        var searchText = $(this).val().toLowerCase();
                        var visibleCount = 0;

                        $('.timeline-row').each(function() {
                            var studentName = $(this).data('student');
                            if (studentName.includes(searchText)) {
                                $(this).show();
                                visibleCount++;
                            } else {
                                $(this).hide();
                            }
                        });

                        $('#visibleCount').text(visibleCount);
                    });

                    // Timeline bar tooltip
                    $('.timeline-bar').tooltip();

                    $('input[type="checkbox"]').change(function() {
                        var isChecked = $(this).is(":checked");
                        if (isChecked) {
                            $(this).closest('tr').addClass('bg-success');
                            var id = $(this).attr('id');
                            localStorage.setItem(id, 'checked');
                        } else {
                            $(this).closest('tr').removeClass('bg-success');
                            var id = $(this).attr('id');
                            localStorage.setItem(id, 'unchecked');
                        }
                    });
                    $(document).ready(function() {
                        $('input[type="checkbox"]').each(function() {
                            var id = $(this).attr('id');
                            var state = localStorage.getItem(id);
                            if (state === 'checked') {
                                $(this).prop('checked', true);
                                $(this).closest('tr').addClass('bg-success');
                            }
                        });
                    });


                })

                // document.addEventListener('DOMContentLoaded', function() {
                //     var checkboxes = document.querySelectorAll('input[type="checkbox"]');
                //     checkboxes.forEach(function(checkbox) {
                //         checkbox.addEventListener('change', function() {
                //             var isChecked = this.checked;
                //             if (isChecked) {
                //                 this.closest('tr').style.backgroundColor = 'green';
                //             } else {
                //                 this.closest('tr').style.backgroundColor = '';
                //             }
                //         });
                //     });
                // });

                $(document).ready(function() {
                    // Initialize if needed
                });

                let currentDemoMonth = 'all';

                function ShowDemoMonth(month) {
                    currentDemoMonth = month;
                    // Update buttons
                    $('.demo-month-btn').removeClass('active');
                    $('#btn-demo-' + month).addClass('active');

                    // Update content blocks
                    $('.demo-month-content').hide();
                    $('#demo-month-block-' + month).fadeIn(300).css('display', 'flex');
                }
            </script>
            <script>
                var groupLabels = [
                    @foreach ($totalForAllMonths as $month => $total)

                        '{{ $month }} ',
                    @endforeach
                ];

                var groupLabels2 = [
                    @foreach ($totalForAllMonths as $month => $total)
                        @if (isset($percentageChangeForAllMonths[$month]))
                            @if ($percentageChangeForAllMonths[$month] > 0)
                                '{{ $month }}\n+{{ $percentageChangeForAllMonths[$month] }}%',
                            @else
                                '{{ $month }}\n{{ $percentageChangeForAllMonths[$month] }}%',
                            @endif
                        @else
                            '{{ $month }}  ',
                        @endif
                    @endforeach
                ];

                // var data = {
                //     labels: groupLabels2,
                //     // label: groupLabels,
                //     datasets: [{
                //         label: 'ยอดรวมแต่ละเดือน ',
                //         data: [
                //             @foreach ($totalForAllMonths as $month => $total)
                //                 {{ $total }},
                //             @endforeach
                //         ],
                //         pointBackgroundColor: 'rgba(52, 143, 226,.65)',
                //         borderColor: 'rgba(52, 143, 226,.65)',
                //         backgroundColor: 'rgba(52, 143, 226,.65)',
                //         pointRadius: 4,
                //         borderWidth: 2,
                //     }, ]
                // };
                var ctx = document.getElementById('lineChart').getContext('2d');
                var lineChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: groupLabels2,
                        // label: groupLabels,
                        datasets: [{
                            label: 'ยอดรวมแต่ละเดือน ',
                            data: [
                                @foreach ($totalForAllMonths as $month => $total)
                                    {{ $total }},
                                @endforeach
                            ],
                            pointBackgroundColor: 'rgba(52, 143, 226,.65)',
                            borderColor: 'rgba(52, 143, 226,.65)',
                            backgroundColor: 'rgba(52, 143, 226,.65)',
                            pointRadius: 4,
                            borderWidth: 2,
                        }, ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index', // Display multiple tooltips at once
                                intersect: false, // Don't intersect with other elements
                            },
                            // title: {
                            //     display: true,
                            //     text: 'Chart Title',
                            // },
                            legend: {
                                display: true,
                                position: 'top',
                            },
                        },
                        animation: {
                            duration: 2000, // Animation duration in milliseconds
                            easing: 'easeInOutQuad', // Easing function
                        },

                    }
                });
            </script>
            <script>
                var groupLabels = [
                    @foreach ($totalForAllMonths as $month => $total)
                        '{{ $month }}',
                    @endforeach
                ];
                var groupLabels2 = [
                    @foreach ($totalForAllMonths as $month => $total)
                        @if (isset($percentageChangeForAllMonths[$month]))
                            @if ($percentageChangeForAllMonths[$month] > 0)
                                '{{ $month }}\n+{{ $percentageChangeForAllMonths[$month] }}%',
                            @else
                                '{{ $month }}\n{{ $percentageChangeForAllMonths[$month] }}%',
                            @endif
                        @else
                            '{{ $month }}  ',
                        @endif
                    @endforeach
                ];

                var data = {
                    labels: groupLabels2,
                    datasets: [{
                        label: 'ยอดรวมแต่ละเดือน ',
                        data: [
                            @foreach ($totalForAllMonths as $month => $total)
                                {{ $total }},
                            @endforeach
                        ],
                        pointBackgroundColor: 'rgba(52, 143, 226,.65)',
                        borderColor: 'rgba(52, 143, 226,.65)',
                        backgroundColor: 'rgba(52, 143, 226,.65)',
                        pointRadius: 4,
                        borderWidth: 2,
                    }]
                };
                var ctx = document.getElementById('barChart').getContext('2d');
                var barChart = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index', // Display multiple tooltips at once
                                intersect: false, // Don't intersect with other elements
                            },
                            // title: {
                            //     display: true,
                            //     text: 'Chart Title',
                            // },
                            legend: {
                                display: true,
                                position: 'top',
                            },
                        },
                        animation: {
                            duration: 2000, // Animation duration in milliseconds
                            easing: 'easeInOutQuad', // Easing function
                        },
                    }
                });
            </script>{{-- ====== PRINT JAVASCRIPT ====== --}}
            <script>
                document.getElementById('doPrint').addEventListener('click', function() {
                    const SECTION_MAP = {
                        'chk-monthly-table': 'monthly-table',
                        'chk-all-month-chart': 'all-month-chart',
                        'chk-timeline': 'timeline',
                        'chk-summary-table': 'summary-table',
                        'chk-demographics': 'demographics',
                        'chk-paper-table': 'paper-table',
                    };

                    const selected = [];
                    for (const [checkboxId, sectionName] of Object.entries(SECTION_MAP)) {
                        const el = document.getElementById(checkboxId);
                        if (el && el.checked) selected.push(sectionName);
                    }

                    if (selected.length === 0) {
                        alert('กรุณาเลือกอย่างน้อย 1 ส่วน');
                        return;
                    }

                    const orientation = document.querySelector('input[name="orientation"]:checked').value;

                    // Get current year from the year selector
                    const yearSelect = document.getElementById('selectMonth');
                    const year = yearSelect ? yearSelect.value : '{{ $year }}';

                    // Close modal
                    const bsModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
                    if (bsModal) bsModal.hide();

                    // Build URL and open in new window
                    const url =
                        `{{ route('reportRecieptStdPrint') }}?year=${year}&sections=${selected.join(',')}&orientation=${orientation}&demo_month=${currentDemoMonth}`;
                    window.open(url, '_blank');
                });
            </script>
        @endsection {{-- @php
                                                    $mark = [];
                                                    $countdata = 0;
                                                    $ignorId = [];
                                                    $groupClass = 'text-red';

                                                    foreach ($groupedData as $data1) {
                                                        $period = explode(' - ', $data1->period);
                                                        if (count($period) == 1) {
                                                            $periods = explode('-', $data1->period);
                                                            $startMonth = date('m', strtotime("{$periods[0]}-{$periods[1]}"));
                                                            $endMonth = count($periods) > 2 ? date('m', strtotime("{$periods[2]}-{$periods[3]}")) : $startMonth;
                                                        } else {
                                                            $startMonth = date('n', strtotime($period[0]));
                                                            $endMonth = date('n', strtotime($period[1]));
                                                        }
                                                        $isInMonthRange = $month >= $startMonth && $month <= $endMonth;

                                                        if ($isInMonthRange === true) {
                                                            if ($data1->group === 'CHN') {
                                                                $groupMark = 'C';
                                                            } elseif ($data1->group === 'Sci') {
                                                                $groupMark = 'S';
                                                            } else {
                                                                $groupMark = $data1->group;
                                                            }
                                                            $groupClass = 'text-success';
                                                        } else {
                                                            $groupMark = '';
                                                        }
                                                        if (!in_array($groupMark, $mark)) {
                                                            $mark[] = $groupMark;
                                                        }
                                                    } //endforeach

                                                @endphp --}} {{-- @php
                                                    $mark = [];
                                                    $countdata = 0;
                                                    $ignorId = [];
                                                    $groupClass = 'text-red';

                                                    foreach ($groupedData as $data1) {
                                                        $period = explode(' - ', $data1->period);
                                                        $currentYear = date('Y');
                                                        if (count($period) == 1) {
                                                            $periods = explode('-', $data1->period);

                                                            $startMonth = date('m', strtotime("{$periods[0]}-{$periods[1]}"));
                                                            $endMonth = count($periods) > 2 ? date('m', strtotime("{$periods[2]}-{$periods[3]}")) : $startMonth;
                                                            $startYear = $periods[0];
                                                            $endYear = $periods[2];
                                                            // dd($periods, $endMonth, $startMonth, $startYear, $endYear);
                                                        } else {
                                                            $periodss = explode('-', $data1->period);
                                                            $startYear = $periods[0];
                                                            $endYear = $periods[2];
                                                            $startMonth = date('m', strtotime("{$periodss[0]}-{$periodss[1]}"));
                                                            $endMonth = date('m', strtotime("{$periodss[2]}-{$periodss[3]}"));
                                                            // dd(
                                                            //     $periodss,
                                                            //     $startMonth,
                                                            //     $endMonth,
                                                            //     // $currentYear
                                                            //     // $startYear,
                                                            //     // $endYear
                                                            // );
                                                        }
                                                        $isInMonthRange = $month >= $startMonth && $startMonth == $currentYear && $month <= $endMonth && $endYear >= $currentYear;
                                                        if ($isInMonthRange === true) {
                                                            if ($data1->group === 'CHN') {
                                                                $groupMark = 'C';
                                                            } elseif ($data1->group === 'Sci') {
                                                                $groupMark = 'S';
                                                            } else {
                                                                $groupMark = $data1->group;
                                                            }
                                                            $groupClass = 'text-success';
                                                        } else {
                                                            $groupMark = '';
                                                        }
                                                        if (!in_array($groupMark, $mark)) {
                                                            $mark[] = $groupMark;
                                                        }
                                                    } //endforeach
                                                @endphp

                                                @php
                                                    $mark = [];
                                                    $countdata = 0;
                                                    $ignorId = [];
                                                    $groupClass = 'text-red';

                                                    foreach ($groupedData as $data1) {
                                                        $period = explode(' - ', $data1->period);
                                                        $currentYear = date('Y');
                                                        if (count($period) == 1) {
                                                            $periods = explode('-', $data1->period);

                                                            $startMonth = date('m', strtotime("{$periods[0]}-{$periods[1]}"));
                                                            $startYear = $periods[0];
                                                            $endMonth = count($periods) > 2 ? date('m', strtotime("{$periods[2]}-{$periods[3]}")) : $startMonth;
                                                            $endYear = count($periods) > 2 ? date('Y', strtotime("{$periods[2]}-{$periods[3]}")) : $startYear;
                                                        } else {
                                                            $periodss = explode('-', $data1->period);
                                                            $startYear = $periodss[0];
                                                            $endYear = $periodss[2];
                                                            $startMonth = date('m', strtotime("{$periodss[0]}-{$periodss[1]}"));
                                                            $endMonth = date('m', strtotime("{$periodss[2]}-{$periodss[3]}"));
                                                        }

                                                        $isInMonthRange = $currentYear == $startYear && $currentYear == $endYear && $month >= $startMonth && $month <= $endMonth;

                                                        if ($isInMonthRange) {
                                                            if ($data1->group === 'CHN') {
                                                                $groupMark = 'C';
                                                            } elseif ($data1->group === 'Sci') {
                                                                $groupMark = 'S';
                                                            } else {
                                                                $groupMark = $data1->group;
                                                            }

                                                            $groupClass = 'text-success';

                                                            if (!in_array($groupMark, $mark)) {
                                                                $mark[] = $groupMark;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                @php
                                                    // use Carbon\Carbon;

                                                    $mark = [];
                                                    $countdata = 0;
                                                    $ignorId = [];
                                                    $groupClass = 'text-red';

                                                    foreach ($groupedData as $data1) {
                                                        $period = explode(' - ', $data1->period);
                                                        $currentYear = date('Y');

                                                        if (count($period) == 1) {
                                                            $periods = explode('-', $data1->period);

                                                            $startMonth = Carbon::parse("{$periods[0]}-{$periods[1]}")->month;
                                                            $startYear = $periods[0];
                                                            $endMonth = count($periods) > 2 ? Carbon::parse("{$periods[2]}-{$periods[3]}")->month : $startMonth;
                                                            $endYear = count($periods) > 2 ? $periods[2] : $startYear;
                                                        } else {
                                                            $periodss = explode('-', $data1->period);
                                                            $startYear = $periodss[0];
                                                            $endYear = $periodss[2];
                                                            $startMonth = Carbon::parse("{$periodss[0]}-{$periodss[1]}")->month;
                                                            $endMonth = Carbon::parse("{$periodss[2]}-{$periodss[3]}")->month;
                                                        }
                                                        dd($startMonth);

                                                        $isInMonthRange = Carbon::now()->year >= $startYear && Carbon::now()->year <= $endYear && Carbon::now()->month >= $startMonth && Carbon::now()->month <= $endMonth;

                                                        if ($isInMonthRange) {
                                                            if ($data1->group === 'CHN') {
                                                                $groupMark = 'C';
                                                            } elseif ($data1->group === 'Sci') {
                                                                $groupMark = 'S';
                                                            } else {
                                                                $groupMark = $data1->group;
                                                            }

                                                            $groupClass = 'text-success';

                                                            if (!in_array($groupMark, $mark)) {
                                                                $mark[] = $groupMark;
                                                            }
                                                        }
                                                    }
                                                @endphp --}}
