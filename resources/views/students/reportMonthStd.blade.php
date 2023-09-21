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
    <style>
        .screen-auto {
            max-width: 100%;
            height: auto;
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

            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <form action="{{ route('search_status') }}" method="GET">
                    @csrf
                    <div class="row mb-5">
                        {{-- <div class="col-lg-2">
                            <div class="row">
                                <label for="month" class="col-lg-3">status : </label>
                                <div class="col-lg-9">
                                    <select name="month" id="selectMonth" class="form-select">
                                        <option value="">เลือกเดือน</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"> {{ date("F", mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div> --}}
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
                                <label for="month" class="col-lg-3">เดือน : </label>
                                <div class="col-lg-9">
                                    <select name="month" id="selectMonth" class="form-select">
                                        <option value="">เลือกเดือน</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"> {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="row">
                                <label for="year" class="col-lg-2">ปี : </label>
                                <div class="col-lg-9">
                                    <select name="year" id="selectMonth" class="form-select">
                                        <option value="">เลือกปี</option>
                                        @php
                                            $currentYear = date('Y');
                                            for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                                echo "<option value='$i'>$i</option>";
                                            }
                                        @endphp
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <button class="btn btn-primary" type="submit" id="seach_status"
                                name="seach_status">ค้นหา</button>
                            {{-- <a href="{{ route('search_status') }}" class="btn btn-primary" id="seach_status" name="seach_status">ค้นหา</a> --}}
                        </div>
                    </div>
                </form>

                {{-- กราฟนี้ใช้ได้ --}}
                {{-- <div id="nv-line-chart" class="h-250px"></div> --}}


                {{-- <canvas id="bar-chart777"></canvas> --}}
                {{-- <canvas id="line-charttest" height="100px"></canvas> --}}
                {{-- <canvas id="line-charttest" width="600" height="150"></canvas> --}}
                {{-- <table style="width: 100%;" id="Listview"
                    class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th class="text-success">New student</th>
                            <th class="text-success">studying . . .</th>
                            <th class="text-warning">Termination of study</th>
                            <th class="text-danger">Drop</th>
                            <th class="text-success">Resume</th>
                            <th class="text-info">None Receipt</th>
                            <th>date time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupStatusCounts = [];
                            foreach ($latestStatusCounts as $record) {
                                $group = $record->group;
                                $status = $record->status;

                                if (!isset($groupStatusCounts[$group])) {
                                    $groupStatusCounts[$group] = [];
                                }
                                if (!isset($groupStatusCounts[$group][$status])) {
                                    $groupStatusCounts[$group][$status] = 0;
                                }
                                $groupStatusCounts[$group][$status]++;
                            }
                            $totalNewStudents = 0;
                            $totalStudying = 0;
                            $totalTermination = 0;
                            $totalDrop = 0;
                            $totalResume = 0;
                            $totalNoneReceipt = 0;
                        @endphp
                        @foreach ($groupStatusCounts as $group => $statusCounts)
                            <tr>
                                <td>

                                    @if ($group === 'M')
                                        Math
                                    @elseif ($group === 'E')
                                        English
                                    @elseif ($group === 'N')
                                        None Receipt
                                    @endif

                                </td>
                                <td>{{ isset($statusCounts['New Student']) ? $statusCounts['New Student'] : 0 }} คน</td>
                                <td>{{ isset($statusCounts['studying . . .']) ? $statusCounts['studying . . .'] : 0 }} คน
                                </td>
                                <td>{{ isset($statusCounts['Termination of study']) ? $statusCounts['Termination of study'] : 0 }}
                                    คน</td>
                                <td>{{ isset($statusCounts['drop']) ? $statusCounts['drop'] : 0 }} คน</td>
                                <td>{{ isset($statusCounts['resume']) ? $statusCounts['resume'] : 0 }} คน</td>
                                <td>{{ isset($statusCounts['None Receipt']) ? $statusCounts['None Receipt'] : 0 }} คน</td>
                                <td>{{ now() }}</td>
                                <!-- You can replace this with the actual date time if needed -->
                            </tr>
                            @php
                                $totalNewStudents += isset($statusCounts['New Student']) ? $statusCounts['New Student'] : 0;
                                $totalStudying += isset($statusCounts['studying . . .']) ? $statusCounts['studying . . .'] : 0;
                                $totalTermination += isset($statusCounts['Termination of study']) ? $statusCounts['Termination of study'] : 0;
                                $totalDrop += isset($statusCounts['drop']) ? $statusCounts['drop'] : 0;
                                $totalResume += isset($statusCounts['resume']) ? $statusCounts['resume'] : 0;
                                $totalNoneReceipt += isset($statusCounts['None Receipt']) ? $statusCounts['None Receipt'] : 0;
                            @endphp
                        @endforeach
                        <tr>
                            <td>Result</td>
                            <td>{{ $totalNewStudents }} คน</td>
                            <td>{{ $totalStudying }} คน</td>
                            <td>{{ $totalTermination }} คน</td>
                            <td>{{ $totalDrop }} คน</td>
                            <td>{{ $totalResume }} คน</td>
                            <td>{{ $totalNoneReceipt }} คน</td>
                            <td>{{ now() }}</td>
                        </tr>

                    </tbody>


                </table> --}}

                <table style="width: 100%;" id="Listview"
                    class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action my-5">

                    <thead>
                        <tr>
                            <th width='1%' class="text-center">no.</th>
                            <th width='1%'>Code</th>
                            <th>Student Nickname</th>
                            <th>Submission Date</th>
                            <th>Reciept Number</th>
                            <th>Group</th>
                            <th>total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $sum = 0;
                        @endphp
                        @foreach ($reportMonth_schoolPrice as $studentId => $groupedData)
                            @foreach ($groupedData as $key => $data)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td class="text-center">{{ $data->std->code }}</td>
                                    <td>{{ $data->std->nick_name }}</td>
                                    <td>{{ $data->rec_school->submission_date }}</td>
                                    <td>{{ $data->rec_school->receipt_number }}</td>
                                    <td>{{ $data->group }}</td>
                                    <td>{{ $data->total }}</td>
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
                            <td class="text-center" colspan="5">sum total</td> <!-- Merged cell -->
                            <td >{{ $sum }}</td>
                        </tr>

                    </tbody>

                </table>

            </div>
        </div>

    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- <script src="https://d3js.org/d3.v7.min.js"></script> --}}
    <script src="../assets/plugins/d3/d3.min.js"></script>
    <script src="../assets/plugins/nvd3/build/nv.d3.min.js"></script>
    <script src="../assets/plugins/chart.js/dist/Chart.min.js"></script>
    <script src="../assets/plugins/flot/source/jquery.canvaswrapper.js"></script>
    <script src="../assets/plugins/flot/source/jquery.colorhelpers.js"></script>
    <script src="../assets/plugins/flot/source/jquery.flot.js"></script>
    <script src="../assets/plugins/flot/source/jquery.flot.saturated.js"></script>
    <script src="../assets/plugins/flot/source/jquery.flot.browser.js"></script>
    <script src="../assets/plugins/flot/source/jquery.flot.drawSeries.js"></script>
    <script src="../assets/plugins/flot/source/jquery.flot.uiConstants.js"></script>

    <script>
        $(document).ready(function() {
            var table1 = $('#Listview').DataTable({
                lengthMenu: [20, 50, 75, 100, 200],
            });

            // let sub_id, std_id;

            // $(document).on('click', '#getViewData', function(e) {
            //     e.preventDefault();

            //     $('.alert-danger').html('');
            //     $('.alert-danger').hide();
            //     $('.alert-success').html('');
            //     $('.alert-success').hide();

            //     let id = $(this).data('id');
            //     [sub_id, std_id] = id.split(',');

            //     $.ajax({
            //         url: "history/" + sub_id + "/" + std_id,
            //         method: 'GET',
            //         success: function(res) {
            //             $('#time_text').html(res.timeText);
            //             $('#date_text').html(res.dateText);
            //             $('#className').html(res.classname);
            //             $('#ViewModalBody').html(res.html);
            //             $('#ViewParent').html(res.parent);
            //             $('#ViewModal').modal('show');
            //         }
            //     });
            // });

        })
    </script>

    {{-- <script>
        var groupLabels = ['New Student', 'Studying', 'Termination of study', 'Drop', 'Resume'];
        var data = [
            @foreach ($groupStatusCounts as $group => $statusCounts)
                @if ($group !== 'N')

                    {
                        label: '{{ $group }}',
                        data: [
                            {{ isset($statusCounts['New Student']) ? $statusCounts['New Student'] : 0 }},
                            {{ isset($statusCounts['studying . . .']) ? $statusCounts['studying . . .'] : 0 }},
                            {{ isset($statusCounts['Termination of study']) ? $statusCounts['Termination of study'] : 0 }},
                            {{ isset($statusCounts['drop']) ? $statusCounts['drop'] : 0 }},
                            {{ isset($statusCounts['resume']) ? $statusCounts['resume'] : 0 }},
                            // {{ isset($statusCounts['None Receipt']) ? $statusCounts['None Receipt'] : 0 }},
                        ]
                    },
                @endif
            @endforeach
        ];
        // var ran2 = Math.floor(Math.random() * 255);
        // var ran3 = Math.floor(Math.random() * 255);
        var ctx = document.getElementById('lineChart').getContext('2d');
        var lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: groupLabels,
                datasets: data.map(function(item) {
                    // var ran1 = Math.floor(Math.random() * 255);
                    // var randomColor = 'rgba(' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ', 1)';
                    var groupColors = {
                        'M': 'rgba(52, 143, 226,.65)',
                        'E': 'rgba(0, 172, 172,.65)',
                        // 'N': 'rgba(255, 99, 132,.65)' // Add more colors if needed
                    };
                    var grouplabels = {
                        'M': 'Math',
                        'E': 'English',
                        // 'N': 'None' // Add more colors if needed
                    };
                    return {
                        label: grouplabels[item.label],
                        data: item.data,
                        pointBackgroundColor: groupColors[item.label],
                        borderColor: groupColors[item.label],
                        backgroundColor: groupColors[item.label],
                        pointRadius: 4,
                        borderWidth: 2,
                        // fill: false
                    }
                })
            },

            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script>
        var groupLabels = ['New Student', 'Studying', 'Termination of study', 'Drop', 'Resume'];
        var data = [
            @foreach ($groupStatusCounts as $group => $statusCounts)
                @if ($group !== 'N')

                    {
                        label: '{{ $group }}',
                        data: [
                            {{ isset($statusCounts['New Student']) ? $statusCounts['New Student'] : 0 }},
                            {{ isset($statusCounts['studying . . .']) ? $statusCounts['studying . . .'] : 0 }},
                            {{ isset($statusCounts['Termination of study']) ? $statusCounts['Termination of study'] : 0 }},
                            {{ isset($statusCounts['drop']) ? $statusCounts['drop'] : 0 }},
                            {{ isset($statusCounts['resume']) ? $statusCounts['resume'] : 0 }},
                            // {{ isset($statusCounts['None Receipt']) ? $statusCounts['None Receipt'] : 0 }},
                        ]
                    },
                @endif
            @endforeach
        ];

        var ctx = document.getElementById('barChart').getContext('2d');
        var barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: groupLabels,
                datasets: data.map(function(item) {
                    var groupColors = {
                        'M': 'rgba(52, 143, 226,.65)',
                        'E': 'rgba(0, 172, 172,.65)',
                        'N': 'rgba(255, 99, 132,.65)' // Add more colors if needed
                    };
                    var grouplabels = {
                        'M': 'Math',
                        'E': 'English',
                        'N': 'None' // Add more colors if needed
                    };
                    return {
                        label: grouplabels[item.label],
                        data: item.data,
                        backgroundColor: groupColors[item.label],
                        borderColor: groupColors[item.label],
                        borderWidth: 1
                    }
                })
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        maxTicksLimit: 5, // Set the maximum number of ticks on the Y-axis
                    }
                }
            }
        });
    </script> --}}
@endsection
