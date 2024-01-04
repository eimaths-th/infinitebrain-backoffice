@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
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
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        {{-- <a class="btn btn-info" data-toggle="modal" data-target=".bd-example-modal-lg" id="ImportButton"><i
                                class="fa fa-file-excel"></i> Import Excel
                        </a>
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New
                            Student</a>
                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a> --}}

                    </div>
                </div>
            </div>
        </div>
        @php
            use Carbon\Carbon;
            $all = 0;
            $remain = 0;
            $qty1 = [];
            $bonus = 0;
            $result_month = 0;
            $result_all = 0;
            $result_bonus = 0;
            $result_his = 0;
            $result_remain = 0;
            $result_prefer = 0;

            $processedIds = [];
            $reset_id = [];
            $current_Month = date('Y-m');

            // $currentMonth = Carbon::createFromFormat('Y-m','2023-10');
            $currentMonth = Carbon::createFromFormat('Y-m', $current_Month);
            // $currentReceiptEndDate = Carbon::createFromFormat('Y-m', '2023-9');
            // dd($currentMonth);

            $currentReceiptEndDate = Carbon::parse($end_date);
        @endphp
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <form action="" name="" id="">
                    @if ($startcourse == null)
                        <h5 class="text-danger"> ไม่พบใบเสร็จ</h5>
                        <h5>code : {{ $std->code }} | NickName : {{ $std->nick_name }}</h5>
                        <h5>Student Name : {{ $std->full_name }}</h5>
                    @else
                        @php
                            if ($std->status == 'กำลังยุติการการเรียน') {
                                $class = 'warning';
                            } elseif ($std->status == 'พ้นสภาพนักเรียน') {
                                $class = 'danger';
                            } else {
                                $class = 'success';
                            }
                        @endphp
                        <h5><span class="text-{{ $class }}">{{ $std->status }}</span> สิ้นสุดที่ {{ $end_date }}
                        </h5>
                        <h5>code : {{ $std->code }} | NickName : {{ $std->nick_name }}
                        </h5>
                        <h5>Student Name : {{ $std->full_name }}</h5>
                    @endif

                    <table style="width: 100%;" id="Listview"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                <th>Suject Name</th>
                                <th>Date</th>
                                <th>Month</th>
                                <th>Prefer</th>
                                <th>Bonus</th>
                                <th>All</th>
                                <th>History</th>
                                <th>Result</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate))
                                <!-- <p class="text-danger">พ้นสภาพนักเรียน</p> -->
                                @foreach ($school_pice as $reset)
                                    @php
                                        $reset_id[] = $reset->id;
                                    @endphp
                                @endforeach
                            @else
                                <!-- <p class="text-danger">กำลังเรียนอยู่</p> -->
                                @foreach ($school_pice as $school_pices)
                                    @if (in_array($school_pices->id, $reset_id))
                                    @else
                                        @php
                                            $course = 0;
                                            $qty = null;
                                            $num_month = null;
                                            $prefer = 0;
                                            $school = $school_pices->qty;
                                            $sub_ids = $school_pices->subject;
                                            $history = $histories->where('student_id', $std->id)->where('subject_id', $sub_ids);
                                            // dd($history);

                                            if (!empty($history)) {
                                                // $his_succsss = count($history);

                                                $history_count = 0;
                                                foreach ($history as $his_check_time) {
                                                    # code...
                                                    // $stime = $his_check_time->stime; //11:15:00
                                                    // $etime = $his_check_time->etime; //12:15:00
                                                    $stime = Carbon::parse($his_check_time->stime); //11:15:00
                                                    $etime = Carbon::parse($his_check_time->etime); //12:15:00
                                                    $hoursDifference = $etime->diffInHours($stime);
                                                    // dd($stime, $etime, $hoursDifference);
                                                    if ($hoursDifference > 1) {
                                                        # code...
                                                        $history_count += 2;
                                                    } else {
                                                        $history_count += 1;
                                                    }
                                                }

                                                $firstRecord = $history->first();

                                                if (!empty($firstRecord) && !in_array($firstRecord->id, $processedIds)) {
                                                    $processedIds[] = $firstRecord->id;
                                                } else {
                                                    $history_count = 0;
                                                }


                                            }
                                            $startcourse = $school_pices->period ?? null;
                                            if ($school_pices->period == $startcourse) {
                                                $num_month = $school;
                                                $course++;
                                            } else {
                                                $num_month += $school;
                                            }
                                            $qty += $school;
                                            if ($num_month > 11) {
                                                $bonus = 12 * $course;
                                                $prefer = $qty * 8;
                                                $all = $qty * 8 + $bonus ?? 'N/A';
                                                $remain = $all - $history_count;
                                            } elseif ($num_month > 5) {
                                                $bonus = 5 * $course;
                                                $prefer = $qty * 8;
                                                $all = $qty * 8 + $bonus ?? 'N/A';
                                                $remain = $all - $history_count;
                                            } elseif ($num_month > 3) {
                                                $bonus = 3 * $course;
                                                $prefer = $qty * 8;
                                                $all = $qty * 8 + $bonus ?? 'N/A';
                                                $remain = $all - $history_count;
                                            } else {
                                                $prefer = $qty * 8;
                                                $all = $qty * 8 ?? 'N/A';
                                                $remain = $all - $history_count;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $school_pices->school_sub->title }}</td>
                                            <td>{{ $school_pices->period }}</td>
                                            <td>{{ $num_month }}</td>
                                            <td>{{ $prefer }} </td>
                                            <td>{{ $bonus }}</td>
                                            <td>{{ $all }}</td>
                                            <td>{{ $history_count }}</td>
                                            <td>{{ $remain }}</td>
                                            <td>
                                                <a href="#" class="btn btn-xs btn-primary btn-edit" id="getViewData"
                                                    data-id="{{ $school_pices->school_sub->id }},{{ $std->id }}"><i
                                                        class="fa fa-eye"></i> View</a>
                                            </td>
                                        </tr>
                                    @endif

                                    @php
                                        $result_month += $num_month;
                                        $result_prefer += $prefer;
                                        $result_all += $all;
                                        $result_bonus += $bonus;
                                        $result_his += $history_count;
                                        $result_remain += $remain;

                                    @endphp
                                @endforeach
                            @endif
                            @php

                            @endphp
                            <tr>
                                <th>Result</th>
                                <th> </th>
                                <th>{{ $result_month }} </th>
                                <th>{{ $result_prefer }} </th>
                                <th> {{ $result_bonus }}</th>
                                <th> {{ $result_all }}</th>
                                <th> {{ $result_his }}</th>
                                <th> {{ $result_remain }}</th>
                                <th> </th>
                            </tr>

                        </tbody>
                    </table>
                </form>
            </div>
        </div>

    </div>

    <div class="body-block-example-2" style="display:none" id="loder_bar">
        <br>
        <div class="loader" id="loder_bar_div">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-border text-danger" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-border text-warning" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <br>
    </div>

    <!-- View  Modal -->
    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="ViewModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">View History Student Class <span
                                id="className"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                            <strong>Success!</strong> Student was edit successfully.
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>

                        {{-- <div class="form-group">
                            <label for="Parent">ชื่อผู้ปกครอง:</label><br>
                            <select style="width: 100%;" class="parent select2 select2_single form-control" id="ViewParent"
                                name="name_parent" multiple="multiple">

                            </select>
                        </div> --}}
                        <div class="row">
                            {{-- date --}}
                            <div class="col-lg-6">
                                <label for="Parent">วันที่ : </label><br>
                                <div class="form-group" id="date_text">
                                    <label for="date"> test date text</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label for="Parent">เวลา : </label><br>
                                <div class="form-group">
                                    <label for="date" id="time_text"> test time text</label>
                                </div>
                            </div>
                        </div>
                        <div id="ViewModalBody">


                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-success" id="SubmitEditForm">Update</button> --}}
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">

                        </div>
                        <div class="col-lg-6">

                        </div>
                    </div>
                </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            //$.noConflict();
            var token = ''
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var table = $('#Listview').DataTable({

                // serverSide: true,
                // processing: true,

                // iDisplayLength: 10,
                lengthMenu: [10, 20, 50, 75, 100],

            });


            let sub_id, std_id;

            $(document).on('click', '#getViewData', function(e) {
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                let id = $(this).data('id');
                [sub_id, std_id] = id.split(',');

                $.ajax({
                    url: "history/" + sub_id + "/" + std_id,
                    method: 'GET',
                    success: function(res) {
                        $('#time_text').html(res.timeText);
                        $('#date_text').html(res.dateText);
                        $('#className').html(res.classname);
                        $('#ViewModalBody').html(res.html);
                        $('#ViewParent').html(res.parent);
                        $('#ViewModal').modal('show');
                    }
                });
            });

        })
    </script>
    <script>
        function openModal() {
            event.preventDefault();
            var modal = document.getElementById("ViewModal");
            modal.style.display = "block";
        }
    </script>
@endsection
