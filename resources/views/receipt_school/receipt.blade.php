@extends('layouts.main_template')

@section('style')
    <!-- CSS ก่อน -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" />

    <!-- JavaScript libraries -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <!-- เปลี่ยนจาก rawgit เป็น cdnjs (rawgit ปิดบริการแล้ว) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script src="plugins/moment/moment.min.js"></script>
    <script src="../plugins/daterangepicker/daterangepicker.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js">
    </script>
    <style>
        input:required:focus:invalid~.form-error-message {
            display: block;
        }

        .form-error-message {
            color: #fff;
            background-color: #f23a3c;
            background-image: url(data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='15' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M7 14.515a7 7 0 110-14 7 7 0 010 14zm-.814-5.86h1.628v-5.21H6.186v5.21zM7 11.259a.82.82 0 00.814-.824A.816.816 0 007 9.631a.813.813 0 100 1.628z' fill='%23fff'/%3E%3C/svg%3E);
            font-size: .75em;
            margin-top: 8px;
            border-radius: 4px;
            background-size: 0.875em;
            background-position: 0.375em;
            background-repeat: no-repeat;
            display: none;
            width: 200px;
            padding: 0.25em 0.5em 0.25em 1.625em;
        }

        .dropzone {
            width: 100%;
            height: 180px;
            min-height: 0px !important;
        }

        .promotion-workflow-panel {
            border: 1px solid #d8e2ef;
            border-radius: 8px;
            background: #f8fbff;
            padding: 14px;
            margin-top: 16px;
            margin-bottom: 16px;
        }

        .promotion-workflow-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .promotion-workflow-title {
            font-weight: 700;
            color: #23364d;
            margin-bottom: 2px;
        }

        .promotion-workflow-subtitle {
            color: #66758a;
            font-size: 12px;
        }

        .promotion-status-pill {
            border: 1px solid #c9d8ea;
            border-radius: 999px;
            color: #40556f;
            background: #ffffff;
            font-size: 12px;
            padding: 4px 10px;
            white-space: nowrap;
        }

        .promotion-context-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d8e2ef;
            border-radius: 6px;
            background: #ffffff;
            padding: 8px 10px;
            margin-bottom: 10px;
        }

        .promotion-state-message {
            border: 1px dashed #c9d8ea;
            border-radius: 6px;
            background: #ffffff;
            color: #66758a;
            padding: 12px;
        }

        .promotion-state-message.is-loading {
            border-style: solid;
            color: #1f6fb2;
            background: #eef7ff;
        }

        .promotion-state-message.is-error {
            border-style: solid;
            color: #a52834;
            background: #fff3f4;
        }

        .campaign-option-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 10px;
        }

        .campaign-option-card {
            cursor: pointer;
            display: block;
            border: 1px solid #d8e2ef;
            border-radius: 8px;
            background: #ffffff;
            padding: 12px;
            min-height: 92px;
        }

        .campaign-option-card.is-selected {
            border-color: #2d7dd2;
            background: #f1f8ff;
        }

        .campaign-option-main {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }

        .campaign-option-name {
            color: #23364d;
            font-weight: 700;
            line-height: 1.3;
            margin-left: 6px;
        }

        .campaign-option-type {
            display: inline-block;
            margin-top: 6px;
            border-radius: 999px;
            background: #eef2f7;
            color: #52657d;
            font-size: 11px;
            padding: 3px 8px;
        }

        .campaign-option-discount {
            color: #0f7a43;
            font-weight: 800;
            white-space: nowrap;
        }

        .campaign-clear-action {
            margin-top: 8px;
        }

        .dropzonex,
        .dropzonex * {
            box-sizing: border-box;
        }

        .dropzonex {
            min-height: 150px;
            background: #e4efff;
            padding: 20px 20px;
            display: none;
        }

        .dropzonex.dz-clickable {
            cursor: pointer;
        }

        .dropzonex.dz-clickable * {
            cursor: default;
        }

        .dropzonex.dz-clickable .dz-message,
        .dropzonex.dz-clickable .dz-message * {
            cursor: pointer;
        }

        .dropzonex.dz-started .dz-message {
            display: none;
        }

        .dropzonex.dz-drag-hover {
            border-style: solid;
        }

        .dropzonex.dz-drag-hover .dz-message {
            opacity: 0.5;
        }

        .dropzonex .dz-message {
            text-align: center;
            margin: 2em 0;
        }

        .dropzonex .dz-preview {
            position: relative;
            display: inline-block;
            vertical-align: top;
            margin: 16px;
            min-height: 100px;
        }

        .dropzonex .dz-preview:hover {
            z-index: 1000;
        }

        .dropzonex .dz-preview:hover .dz-details {
            opacity: 1;
        }

        .dropzonex .dz-preview.dz-file-preview .dz-image {
            border-radius: 20px;
            background: #999;
            background: linear-gradient(to bottom, #eee, #ddd);
        }

        .dropzonex .dz-preview.dz-file-preview .dz-details {
            opacity: 1;
        }

        .dropzonex .dz-preview.dz-image-preview {
            background: #e4efff;
        }

        .dropzonex .dz-preview.dz-image-preview .dz-details {
            -webkit-transition: opacity 0.2s linear;
            -moz-transition: opacity 0.2s linear;
            -ms-transition: opacity 0.2s linear;
            -o-transition: opacity 0.2s linear;
            transition: opacity 0.2s linear;
        }

        .dropzonex .dz-preview .dz-remove {
            font-size: 14px;
            text-align: center;
            display: block;
            cursor: pointer;
            border: none;
        }

        .dropzonex .dz-preview .dz-remove:hover {
            text-decoration: underline;
        }

        .dropzonex .dz-preview:hover .dz-details {
            opacity: 1;
        }

        .dropzonex .dz-preview .dz-details {
            z-index: 20;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            font-size: 13px;
            min-width: 100%;
            max-width: 100%;
            padding: 2em 1em;
            text-align: center;
            color: rgba(0, 0, 0, 0.9);
            line-height: 150%;
        }

        .dropzonex .dz-preview .dz-details .dz-size {
            margin-bottom: 1em;
            font-size: 16px;
        }

        .dropzonex .dz-preview .dz-details .dz-filename {
            white-space: nowrap;
        }

        .dropzonex .dz-preview .dz-details .dz-filename:hover span {
            border: 1px solid rgba(200, 200, 200, 0.8);
            background-color: rgba(255, 255, 255, 0.8);
        }

        .dropzonex .dz-preview .dz-details .dz-filename:not(:hover) {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dropzonex .dz-preview .dz-details .dz-filename:not(:hover) span {
            border: 1px solid transparent;
        }

        .dropzonex .dz-preview .dz-details .dz-filename span,
        .dropzonex .dz-preview .dz-details .dz-size span {
            background-color: rgba(255, 255, 255, 0.4);
            padding: 0 0.4em;
            border-radius: 3px;
        }

        .form_none {
            display: none !important;
        }

        .dropzonex .dz-preview:hover .dz-image img {
            -webkit-transform: scale(1.05, 1.05);
            -moz-transform: scale(1.05, 1.05);
            -ms-transform: scale(1.05, 1.05);
            -o-transform: scale(1.05, 1.05);
            transform: scale(1.05, 1.05);
            -webkit-filter: blur(8px);
            filter: blur(8px);
        }

        .dropzonex .dz-preview .dz-image {
            border-radius: 20px;
            overflow: hidden;
            width: 120px;
            height: 120px;
            position: relative;
            display: block;
            z-index: 10;
        }

        .dropzonex .dz-preview .dz-image img {
            display: block;
        }

        .dropzonex .dz-preview.dz-success .dz-success-mark {
            -webkit-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
            -moz-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
            -ms-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
            -o-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
            animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .dropzonex .dz-preview.dz-error .dz-error-mark {
            opacity: 1;
            -webkit-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
            -moz-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
            -ms-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
            -o-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
            animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .dropzonex .dz-preview .dz-success-mark,
        .dropzonex .dz-preview .dz-error-mark {
            pointer-events: none;
            opacity: 0;
            z-index: 500;
            position: absolute;
            display: block;
            top: 50%;
            left: 50%;
            margin-left: -27px;
            margin-top: -27px;
        }




        .dropzonex .dz-preview.dz-processing .dz-progress {
            opacity: 1;
            -webkit-transition: all 0.2s linear;
            -moz-transition: all 0.2s linear;
            -ms-transition: all 0.2s linear;
            -o-transition: all 0.2s linear;
            transition: all 0.2s linear;
        }

        .dropzonex .dz-preview.dz-complete .dz-progress {
            opacity: 0;
            -webkit-transition: opacity 0.4s ease-in;
            -moz-transition: opacity 0.4s ease-in;
            -ms-transition: opacity 0.4s ease-in;
            -o-transition: opacity 0.4s ease-in;
            transition: opacity 0.4s ease-in;
        }

        .dropzonex .dz-preview:not(.dz-processing) .dz-progress {
            -webkit-animation: pulse 6s ease infinite;
            -moz-animation: pulse 6s ease infinite;
            -ms-animation: pulse 6s ease infinite;
            -o-animation: pulse 6s ease infinite;
            animation: pulse 6s ease infinite;
        }

        .dropzonex .dz-preview .dz-progress {
            opacity: 1;
            z-index: 1000;
            pointer-events: none;
            position: absolute;
            height: 16px;
            left: 50%;
            top: 50%;
            margin-top: -8px;
            width: 80px;
            margin-left: -40px;
            background: rgba(255, 255, 255, 0.9);
            -webkit-transform: scale(1);
            border-radius: 8px;
            overflow: hidden;
        }

        .dropzonex .dz-preview .dz-progress .dz-upload {
            background: #333;
            background: linear-gradient(to bottom, #666, #444);
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 0;
            -webkit-transition: width 300ms ease-in-out;
            -moz-transition: width 300ms ease-in-out;
            -ms-transition: width 300ms ease-in-out;
            -o-transition: width 300ms ease-in-out;
            transition: width 300ms ease-in-out;
        }

        .dropzonex .dz-preview.dz-error .dz-error-message {
            display: block;
        }

        .dropzonex .dz-preview.dz-error:hover .dz-error-message {
            opacity: 1;
            pointer-events: auto;
        }

        .dropzonex .dz-preview .dz-error-message {
            pointer-events: none;
            z-index: 1000;
            position: absolute;
            display: block;
            display: none;
            opacity: 0;
            -webkit-transition: opacity 0.3s ease;
            -moz-transition: opacity 0.3s ease;
            -ms-transition: opacity 0.3s ease;
            -o-transition: opacity 0.3s ease;
            transition: opacity 0.3s ease;
            border-radius: 8px;
            font-size: 13px;
            top: 130px;
            left: -10px;
            width: 140px;
            background: #be2626;
            background: linear-gradient(to bottom, #be2626, #a92222);
            padding: 0.5em 1.2em;
            color: white;
        }

        .dropzonex .dz-preview .dz-error-message:after {
            content: '';
            position: absolute;
            top: -6px;
            left: 64px;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #be2626;
        }

        .modal-xxl {
            max-width: 1600px !important;
        }
    </style>
@endsection




@section('content')
    <div class="row">

        @if ($message = Session::get('success'))
            <script>
                toastr.success('{{ $message }}', {
                    timeOut: 5000
                });
            </script>
        @endif

        @if ($message = Session::get('error'))
            <script>
                toastr.error('{{ $message }}', {
                    timeOut: 5000
                });
            </script>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-note2 icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>Receipts
                        <div class="page-title-subheading">Infinite Brain Receipt Data</div>
                    </div>
                </div>

                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New
                            Receipt</a>
                        {{-- <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a>

                    </div>
                    <div class="d-inline-block dropdown">
                        <div class="dropdown show">
                            <button class="btn btn-shadow btn-secondary dropdown-toggle" href="#" role="button"
                                id="dropdownMenuLink" data-toggle="dropdown" autoClose="true" aria-haspopup="true"
                                aria-expanded="false">
                                <span class="btn-icon-wrapper pr-2 opacity-7">
                                    <i class="fa fa-print fa-w-20"></i>
                                </span>
                                Print ใบปะหน้า
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <li><a class="dropdown-item" href="#" id="printr1">ใบปะหน้ารายวัน</a></li>
                                <li><a class="dropdown-item" href="#" id="printr2">ใบปะหน้ารายเดือน</a></li>
                            </ul>
                        </div>

                    </div> --}}



                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card" id="filter_card">
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="col-md col-sm-6">
                        <div class="position-relative form-group">
                            <label for="type_filter">ประเภทเอกสาร:</label>
                            <select id="type_filter" class="form-control">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="0">Normal Receipt</option>
                                <option value="2">Credit Note</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md col-sm-6">
                        <div class="position-relative form-group">
                            <label for="filter_date_start">วันที่เริ่ม:</label>
                            <input type="text" class="FilterDate form-control" id="filter_date_start"
                                placeholder="DD/MM/YYYY" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md col-sm-6">
                        <div class="position-relative form-group">
                            <label for="filter_date_end">วันที่ถึง:</label>
                            <input type="text" class="FilterDate form-control" id="filter_date_end"
                                placeholder="DD/MM/YYYY" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md col-sm-6">
                        <div class="position-relative form-group">
                            <label for="filter_promotion">Promotion:</label>
                            <select id="filter_promotion" class="form-control">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach ($promotions as $promotion)
                                    <option value="{{ $promotion->id }}">{{ $promotion->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md col-sm-6">
                        <div class="position-relative form-group">
                            <label for="filter_payment">Payment Type:</label>
                            <select id="filter_payment" class="form-control">
                                <option value="">-- ทั้งหมด --</option>
                                @foreach ($datap as $keyp)
                                    <option value="{{ $keyp['id'] }}">{{ $keyp['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md col-sm-6">
                        <div class="position-relative form-group">
                            <label for="filter_source">Source:</label>
                            <select id="filter_source" class="form-control">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Line">Line</option>
                                <option value="Website">Website</option>
                                <option value="Walk-in">Walk-in</option>
                                <option value="คนรู้จักแนะนำ">คนรู้จักแนะนำ</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-auto col-sm-12 mt-3">
                        <div class="form-group mb-0">
                            <button type="button" class="btn btn-success text-nowrap" id="filter_apply">
                                <i class="fa fa-search"></i> ค้นหา
                            </button>
                            <button type="button" class="btn btn-secondary text-nowrap" id="filter_clear">
                                <i class="fa fa-eraser"></i> ล้างค่า
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <form method="post" action="{{ route('receipts.destroy_all') }}" name="delete_all" id="delete_all">
                    @csrf
                    @method('DELETE')
                    <table style="width: 100%;" id="Listview"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                <th width="10px"><input type="checkbox" id="check-all" class="flat"></th>
                                <th>Type</th>
                                <th>Promotion</th>
                                <th>Promotion Name</th>
                                <th>Submission Date</th>
                                <th>Voucher Number</th>
                                <th>Parent Name</th>

                                <th>Mobile</th>
                                <th>Address</th>
                                <th>Postcode</th>
                                <th width="150px">Student</th>
                                <th width="1%" class="text-nowrap">Action</th>
                            </tr>
                        </thead>

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

    <!-- PrintDay Modal -->
    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="PrintDayModal">
        <div class="modal-dialog modal-lg" role="document">
            <form id="PrintDayForm" class="form" action="{{ route('receipts.print_day') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Print ใบปะหน้ารายวัน</h5>
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

                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="File">สนามสอบ:</label>

                                <input type="hidden" name="type" value="view">
                                <!-- <input type="hidden" name="pdsfield" value="{{ request()->get('sfield') }}"> -->
                            </div>
                            <div class="form-group">
                                <label for="Submission Date">Print Date:</label>
                                <input type="text" class="AddDate form-control" name="print_date"
                                    id="PrintDaySubmissiondate" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="Submission Date">รหัสเอกสาร:</label>
                                <input type="number" class="form-control" name="print_number" id="PrintDayNumber"
                                    value="" required>
                            </div>
                            <div class="form-group">
                                <label for="font">ขนาดตัวอักษร:</label>
                                <select class="form-control" id="PrintDaysFont" name="fontsize" required>
                                    <option value="1"> เล็กมาก </option>
                                    <option value="2" selected> เล็ก </option>
                                    <option value="3"> ปานกลาง </option>
                                    <option value="4"> ใหญ่ </option>

                                </select>
                                <input type="hidden" name="type" value="view">
                                <!-- <input type="hidden" name="pdsfield" value="{{ request()->get('sfield') }}"> -->
                            </div>
                        </div>


                    </div>
                    <!-- Modal footer id="SubmitPrintDayForm"-->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Print</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PrintMonth Modal -->
    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="PrintMonthModal">
        <div class="modal-dialog modal-lg" role="document">
            <form id="PrintMonthForm" class="form" action="{{ route('receipts.print_month') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Print ใบปะหน้ารายเดือน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="display: none;">

                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="File">สนามสอบ:</label>

                                <input type="hidden" name="type" value="view">
                                <!-- <input type="hidden" name="pmsfield" value="{{ request()->get('sfield') }}"> -->
                            </div>
                            <div class="form-group">
                                <label for="Submission Month">Print Month:</label>
                                <input type="text" class="AddMonth form-control" name="print_month"
                                    id="PrintMonthSubmissiondate" value="" required>
                            </div>

                            {{-- <div class="form-group">
                             <label for="Submission Date">รหัสเอกสาร:</label>
                             <input type="number" class="form-control" name="print_number" id="PrintDayNumber"
                                 value="" required>
                         </div> --}}
                        </div>


                    </div>
                    <!-- Modal footer id="SubmitPrintDayForm"-->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Print</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="ImportModal">
        <div class="modal-dialog modal-lg" role="document">
            <form id="importForm" class="form" action="{{ route('receipts.import') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Import Receipt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="display: none;">

                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="File">สนามสอบ:</label>

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="File">Import File:</label>
                            <input type="file" name="import_file" id="import_file" required />
                        </div>

                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitImportForm">import</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Create Modal -->

    <div class="fade modal " tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="CreateModal">
        <div class="modal-dialog modal-xxl" style="width:100%;" role="document">
            <form id="infinite" class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Add </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">

                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="CreateModalBody">
                            <ul class="nav nav-tabs">
                                <li class="nav-item"><a data-toggle="tab" href="#tab-eg10-0"
                                        class="active nav-link">ข้อมูลลูกค้า</a></li>
                                <li class="nav-item"><a data-toggle="tab" href="#tab-eg10-1"
                                        class="nav-link">รายการในใบเสร็จ</a></li>

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab-eg10-0" role="tabpanel">
                                    <div class="form-row">

                                        <!-- <div class="col-md-12 ">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <select class="form-select" name="type" id="type" style="width:300px">

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <option value="1" hidden disabled>ใบกำกับภาษี/ใบเสร็จรับเงิน</option>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <option value="2">ใบลดหนี้</option>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </select>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div> -->

                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="Submission Date">Submission Date:</label>
                                                <input type="text" class="AddDate form-control" name="submission_date"
                                                    id="AddSubmissiondate" value="">
                                            </div>
                                        </div>
                                        <!-- <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="position-relative  form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <label for="Package">Package:</label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <input type="text" class="form-control" name="package" id="AddPackage"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div> -->
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="Payment">Payment Type:</label>
                                                <select style="width: 100%;" class="form-control" id="AddPayment"
                                                    name="payment" required>
                                                    <option value="" selected>Select Payment</option>
                                                    @foreach ($datap as $keyp)
                                                        <option value="{{ $keyp['id'] }}">{{ $keyp['name'] }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="AddIsVat">ภาษีมูลค่าเพิ่ม (VAT 7%):</label>
                                                <div class="form-check" style="padding-top: 5px;">
                                                    <input type="checkbox" class="form-check-input" id="AddIsVat" name="is_vat" value="1" checked>
                                                    <label class="form-check-label" for="AddIsVat">คิดภาษี (Default)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="AddPromotion">Promotion:</label>
                                                <select style="width: 100%;" class="form-control" id="AddPromotion"
                                                    name="promotion_id" required>
                                                    <option value="">-- Select Promotion --</option>
                                                    @foreach ($promotions as $promotion)
                                                        <option value="{{ $promotion->id }}">{{ $promotion->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-4">
                                            <div class="position-relative form-group">
                                                <label for="Source">แหล่งที่มา (Source):</label>
                                                <select class="form-control" id="AddSource" name="source">
                                                    <option value="">-- เลือกแหล่งที่มา --</option>
                                                    <option value="Facebook">Facebook</option>
                                                    <option value="Line">Line</option>
                                                    <option value="Website">Website</option>
                                                    <option value="Walk-in">Walk-in</option>
                                                    <option value="คนรู้จักแนะนำ">คนรู้จักแนะนำ</option>
                                                    <option value="อื่นๆ">อื่นๆ (ระบุในหมายเหตุ)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="AddReferentParentWrapper" style="display: none;">
                                            <div class="position-relative form-group">
                                                <label for="AddReferentParentId">ผู้แนะนำ:</label>
                                                <select class="form-control" id="AddReferentParentId" name="referent_parent_id">
                                                    <option value="">-- เลือกผู้แนะนำ --</option>
                                                    @foreach ($data4 ?? [] as $referent_parent)
                                                        <option value="{{ $referent_parent->id }}">
                                                            {{ $referent_parent->code }} {{ $referent_parent->name_parent }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative form-group">
                                                <label for="Remark">หมายเหตุ (Remark):</label>
                                                <textarea class="form-control" id="AddRemark" name="remark" rows="1" placeholder="ระบุรายละเอียดเพิ่มเติม"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Parent">Name Parent:</label><br>
                                                <select style="width: 100%;"
                                                    class="parent select2 select2_single form-control" id="AddParent"
                                                    name="name_parent" multiple="multiple">
                                                    <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <option value="" selected>Select Parent</option>-->
                                                    @foreach ($data4 as $key2)
                                                        <option value="{{ $key2->id }}">{{ $key2->code }}
                                                            {{ $key2->name_parent }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Mobile">Mobile:</label>
                                                <input type="text" class=" form-control" name="mobile"
                                                    id="AddMobile" value="" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="form-row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="position-relative form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <label for="City">City:</label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <input type="text" class="form-control" name="city" id="AddCity" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="position-relative form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <label for="Province">Province:</label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <input type="text" class="form-control" name="province" id="AddProvince" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div> -->

                                    <div class="form-group">
                                        <label for="Name">Address:</label>
                                        <textarea class="form-control" name="address" id="AddAddress" value="" disabled></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="Postcode">Postcode:</label>
                                        <input type="text" class="form-control" name="postcode" id="AddPostcode"
                                            value="" disabled>
                                    </div>




                                    <div class="form-group">
                                        <label for="Student">Student:</label><br>
                                        <select style="width: 100%;" class="select2 select2_multiple form-control"
                                            id="AddStudent" name="student[]" multiple="multiple">
                                            {{-- <!-- <option value="" selected>Select Student</option> -->
                                            <!-- @foreach ($data2 as $key2)
                                        <option value="{{ $key2->id }}">{{ $key2->code }} {{ $key2->first_name }} {{ $key2->last_name }}
                                                                                                                                                                                                        </option>
                                            @endforeach --> --}}

                                        </select>
                                    </div>

                                    <br>

                                    <div class="form-row form-focus" id="dropzone-att">
                                        <div class="col-md-12 col-sm-12">
                                            <a id="datt"></a>
                                            <div class="position-relative form-group">
                                                <label class="form-label form-label-top form-label-auto"
                                                    for="att">อัพโหลดหลักฐานการชำระเงิน<span
                                                        class="form-required">*</span>
                                                    <div style="width: 280px"></div>
                                                </label>
                                                <input type="text" id="drop2" name="drop2"
                                                    class="form-control form_none" value="" required>
                                                <div class="dropzone" id="my-awesome-dropzone2"
                                                    style="font-size: 1.5em;">
                                                    <div class="fallback">

                                                    </div>
                                                </div>
                                                <label class="form-sub-label" style="min-height:13px"
                                                    aria-hidden="false">Receipt Photo</label>
                                                <div id="dropzone-att-error" class="form-error-message" role="alert">
                                                    <img src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                        height="10">
                                                    <span class="error-navigation-message">This field is
                                                        required.</span>
                                                    <div class="form-error-arrow">
                                                        <div class="form-error-arrow-inner"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropzonex dz-default dz-message" id="dropzone_preview2"
                                        style="font-size: 1.5em;">
                                        <h3 class="dropzone-previews ui"></h3>
                                    </div>

                                </div>
                                <div class="tab-pane" id="tab-eg10-1" role="tabpanel">

                                    <div class="promotion-workflow-panel">
                                        <div class="promotion-workflow-header">
                                            <div>
                                                <div class="promotion-workflow-title">Campaign ส่วนลด</div>
                                                <div class="promotion-workflow-subtitle">Campaign ที่ตรงกับ Promotion และรายการเรียน (นักเรียนใหม่คำนวณแยกตามวิชา)</div>
                                            </div>
                                            <span class="promotion-status-pill">คำนวณอัตโนมัติ</span>
                                        </div>
                                        <input type="hidden" id="CreateCalculatedPromotionCampaignId" value="">
                                        <label class="promotion-context-toggle" for="CreateIsNewStudentPromotionContext">
                                            <input type="checkbox" id="CreateIsNewStudentPromotionContext" value="1" disabled>
                                            <span>นักเรียนใหม่</span>
                                        </label>
                                        <div id="CreateCalculatedPromotionResult" class="small text-muted">
                                            <div class="promotion-state-message">เลือก Promotion เพื่อดู Campaign ที่ใช้ได้</div>
                                        </div>
                                    </div>

                                    <table id="myTbl"
                                        class="table table-striped table-bordered responsive-utilities jambo_table "
                                        width="90%" align="center">
                                        <thead>

                                            <tr class="headings">

                                                <th class="column-title"> NO.</th>
                                                <th class="column-title"> Student Name</th>
                                                <th class="column-title"> Course Type</th>
                                                <th class="column-title"> Subject</th>
                                                <th class="column-title"> Start Level</th>
                                                <th class="column-title"> Time</th>
                                                <th class="column-title"> Qty</th>
                                                <th class="column-title"> Book/ <br> สื่อ</th>
                                                <th class="column-title"> Book/ <br> สื่อ Price</th>
                                                <th class="column-title abc">Type</th>
                                                <th class="column-title"> Discount</th>
                                                <th class="column-title"> Price</th>
                                                <th class="column-title"> Period/รายละเอียดสื่อ</th>
                                                <th class="column-title"> Total</th>
                                                {{-- <th class="column-title"> </th> --}}
                                            </tr>

                                        </thead>

                                        <tbody>
                                            <tr class="firstTr" style="display:none" id="tr1">
                                                <td width="4%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown" name="flown"
                                                            class="form-control has-feedback-left" value="1"
                                                            required="required">

                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="select2 form-control stdrec "
                                                            id="std_id" name="student_id">
                                                            <option value="">เลือกชื่อนักเรียน</option>

                                                        </select>

                                                    </div>

                                                </td>

                                                <td width="7%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="ctype form-control"
                                                            id="course_type" name="course_type" required>
                                                            <option value="1">Course+Book
                                                            </option>
                                                            <option value="2">Book
                                                            </option>
                                                            <option value="3">สื่อการสอน
                                                            </option>
                                                        </select>

                                                    </div>

                                                </td>

                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="aaa form-control"
                                                            id="subject_id" name="subject_id" required>
                                                            <option value="" selected>Select Subject</option>

                                                            @foreach ($data3 as $key2)
                                                                <option value="{{ $key2->id }}">
                                                                    {{ $key2->title }}
                                                                </option>
                                                            @endforeach


                                                        </select>
                                                    </div>
                                                </td>

                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class=" form-control" id="level_id"
                                                            name="level_id">



                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <select style="width: 100%;" class=" form-control" id="week"
                                                            name="week" required>
                                                            <option value="1" selected></option>

                                                            <option value="1">1 week
                                                            </option>
                                                            <option value="2">2 week
                                                            </option>
                                                            <option value="3">3 week
                                                            </option>

                                                            <option value="4">Month
                                                            </option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="4%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class=" form-control" id="qty"
                                                            name="qty" required>
                                                            <option value="" selected></option>

                                                            <option value="1">1
                                                            </option>
                                                            <option value="2">2
                                                            </option>
                                                            <option value="3">3
                                                            </option>
                                                            <option value="4">4
                                                            </option>
                                                            <option value="5">5
                                                            </option>
                                                            <option value="6">6
                                                            </option>
                                                            <option value="7">7
                                                            </option>
                                                            <option value="8">8
                                                            </option>
                                                            <option value="9">9
                                                            </option>
                                                            <option value="10">10
                                                            </option>
                                                            <option value="11">11
                                                            </option>
                                                            <option value="12">12
                                                            </option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <input type="number" class="form-control" name="book"
                                                            id="book" value="">

                                                    </div>
                                                </td>
                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 cc">
                                                        <input type="text" id="book_price" name="book_price"
                                                            class="form-control has-feedback-left" value="">

                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-xs-12 cc">
                                                        <input type="text" id="media_price" name="media_price"
                                                            class="form-control has-feedback-left" value=""
                                                            placeholder="ราคาสื่อการสอน" autocomplete="off">

                                                    </div>
                                                </td>
                                                <td width="7%" class="aabbcc">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 pm">
                                                        <select style="width: 100%;" class=" form-control" id="type"
                                                            name="type">
                                                            <option value="0" selected>Select Type</option>

                                                            {{-- <!-- <option value="{{ $key2->id }}">{{ $key2->title }}</option> --> --}}
                                                            <option value="1">One Tpye แบบเดี่ยว
                                                            </option>
                                                            <option value="2">Couple แบบคู่
                                                            </option>


                                                        </select>
                                                    </div>
                                                </td>

                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <input type="text" class="form-control" name="discount"
                                                            id="discount" value="0">

                                                    </div>
                                                </td>
                                                <td width="7%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="bbb form-control"
                                                            id="price" name="price" required>
                                                            <option value="" selected>Select Price</option>

                                                            <option value="1">Old Price
                                                            </option>
                                                            <option value="2">New Price
                                                            </option>

                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        {{-- <input type="text" class="form-control" name="period"
                                                                id="period" value=""> --}}
                                                        <input type="text"
                                                            class="form-control more_reservation1 float-right"
                                                            id="period" name="period">

                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <input type="text" class="form-control" name="media_name"
                                                            id="media_name" value="" placeholder="ชื่อสื่อการสอน"
                                                            autocomplete="off">

                                                    </div>
                                                </td>

                                                <td width="8%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 pp">
                                                    </div>
                                                </td>


                                                {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                            </tr>
                                            <tr class="firstTr">
                                                <td width="4%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value="1"
                                                            required="required">

                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="select2 form-control stdrec "
                                                            id="std_id" name="student_id">
                                                            <option value="">เลือกชื่อนักเรียน</option>

                                                        </select>
                                                    </div>
                                                </td>

                                                <td width="9%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="ctype form-control"
                                                            id="course_type" name="course_type" required>
                                                            <option value="1">Course+Book
                                                            </option>
                                                            <option value="2">Book
                                                            </option>
                                                            <option value="3">สื่อการสอน
                                                            </option>
                                                        </select>

                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-xs-12" id="entrance_fee">
                                                        <div class="form-check">
                                                            <input class="form-check-input" id="entrance_fee_val"
                                                                type="checkbox" name="entrance_fee" value="535" />
                                                            <label class="form-check-label"
                                                                for="entrance_fee">ค่าแรกเข้า</label>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="aaa form-control"
                                                            id="subject_id" name="subject_id" required>
                                                            <option value="" selected>Select Subject</option>

                                                            @foreach ($data3 as $key2)
                                                                <option value="{{ $key2->id }}">
                                                                    {{ $key2->title }}
                                                                </option>
                                                            @endforeach


                                                        </select>
                                                    </div>
                                                </td>

                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class=" form-control" id="level_id"
                                                            name="level_id">
                                                            <option value="">select level</option>


                                                        </select>
                                                    </div>
                                                </td>

                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class=" form-control" id="week"
                                                            name="week" required>
                                                            <option value="1" selected></option>

                                                            <option value="1">1 week
                                                            </option>
                                                            <option value="2">2 week
                                                            </option>
                                                            <option value="3">3 week
                                                            </option>

                                                            <option value="4">Month
                                                            </option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="4%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class=" form-control" id="qty"
                                                            name="qty" required>
                                                            <option value="" selected></option>
                                                            <option value="1">1
                                                            </option>
                                                            <option value="2">2
                                                            </option>
                                                            <option value="3">3
                                                            </option>
                                                            <option value="4">4
                                                            </option>
                                                            <option value="5">5
                                                            </option>
                                                            <option value="6">6
                                                            </option>
                                                            <option value="7">7
                                                            </option>
                                                            <option value="8">8
                                                            </option>
                                                            <option value="9">9
                                                            </option>
                                                            <option value="10">10
                                                            </option>
                                                            <option value="11">11
                                                            </option>
                                                            <option value="12">12
                                                            </option>

                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <input type="number" class="form-control" name="book"
                                                            id="book" value="">

                                                    </div>
                                                </td>
                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 cc">
                                                        <input type="text" id="book_price" name="book_price"
                                                            class="form-control has-feedback-left" value="">

                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-xs-12 cc">
                                                        <input type="text" id="media_price" name="media_price"
                                                            class="form-control has-feedback-left"
                                                            placeholder="ราคาสื่อการสอน" value=""
                                                            autocomplete="off">

                                                    </div>
                                                </td>
                                                <td width="7%" class="aabbcc">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 pm">
                                                        <select style="width: 100%;" class=" form-control" id="type"
                                                            name="type">
                                                            <option value="0" selected>Select Type</option>

                                                            <!-- <option value="{{ $key2->id }}">{{ $key2->title }}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </option> -->
                                                            <option value="1">One Tpye แบบเดี่ยว
                                                            </option>
                                                            <option value="2">Couple แบบคู่
                                                            </option>


                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="5%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <input type="number" class="form-control" name="discount"
                                                            id="discount" value="0">

                                                    </div>
                                                </td>
                                                <td width="7%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <select style="width: 100%;" class="bbb form-control"
                                                            id="price" name="price" required>
                                                            <option value="" selected>Select Price</option>

                                                            <option value="1">Old Price
                                                            </option>
                                                            <option value="2">New Price
                                                            </option>

                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        {{-- <input type="text" class="form-control" name="period"
                                                                id="period" value="" required> --}}

                                                        <input type="text" class="form-control datePeriod float-right"
                                                            id="period" name="period">


                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                        <input type="text" class="form-control" name="media_name"
                                                            id="media_name" value="" placeholder="ชื่อสื่อการสอน"
                                                            autocomplete="off">

                                                    </div>
                                                </td>


                                                <td width="8%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 pp">
                                                    </div>
                                                </td>


                                                {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                            </tr>
                                        </tbody>


                                    </table>
                                    <div class="col-md-12" align="right">

                                        <button type="button" id="addRow" class="btn btn-sm btn-primary btnAddg"><i
                                                class="fa fa-plus"></i></button>
                                        <button type="button" id="removeRow" class="btn btn-sm btn-danger btnRemoveg"><i
                                                class="fa fa-minus"></i></button>
                                    </div>

                                </div>
                            </div>
                            <!--tab-->
                        </div>

                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitCreateForm">Save</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit  Modal -->
    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="EditModal">
        <div class="modal-dialog modal-xxl" role="document">
            <form id="infiniteEdit" class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">

                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <strong>Success!</strong> Receipt was edit successfully.
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="EditModalBody">
                            <ul class="nav nav-tabs">
                                <li class="nav-item"><a data-toggle="tab" href="#tab-eg20-0"
                                        class="active nav-link">ข้อมูลลูกค้า</a></li>
                                <li class="nav-item"><a data-toggle="tab" href="#tab-eg20-1"
                                        class="nav-link">รายการในใบเสร็จ</a></li>

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab-eg20-0" role="tabpanel">
                                    <input type="text" class="form-control" name="id_parent" id="id_parent"
                                        value="" readonly hidden>

                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="ReceiptNumber">Voucher Number:</label>
                                                <input type="text" class="form-control" name="receipt_number"
                                                    id="receipt_number" value="" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative  form-group">
                                                <label for="ReceiptCNumber">Customer Code:</label>
                                                <input type="text" class="form-control" name="customer_code"
                                                    id="customer_code" value="" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="Submission Date">Submission Date:</label>
                                                <input type="text" class="AddDate form-control" name="submission_date"
                                                    id="EditSubmissiondate" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="Payment">Payment Type:</label>
                                                <select style="width: 100%;" class="form-control" id="EditPayment"
                                                    name="payment" required>
                                                    <option value="">Select Payment</option>


                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="EditIsVat">ภาษีมูลค่าเพิ่ม (VAT 7%):</label>
                                                <div class="form-check" style="padding-top: 5px;">
                                                    <input type="checkbox" class="form-check-input" id="EditIsVat" name="is_vat" value="1">
                                                    <label class="form-check-label" for="EditIsVat">คิดภาษี</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="EditPromotion">Promotion:</label>
                                                <select style="width: 100%;" class="form-control" id="EditPromotion"
                                                    name="promotion_id">
                                                    <option value="">-- Select Promotion --</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-4">
                                            <div class="position-relative form-group">
                                                <label for="Source">แหล่งที่มา (Source):</label>
                                                <select class="form-control" id="EditSource" name="source">
                                                    <option value="">-- เลือกแหล่งที่มา --</option>
                                                    <option value="Facebook">Facebook</option>
                                                    <option value="Line">Line</option>
                                                    <option value="Website">Website</option>
                                                    <option value="Walk-in">Walk-in</option>
                                                    <option value="คนรู้จักแนะนำ">คนรู้จักแนะนำ</option>
                                                    <option value="อื่นๆ">อื่นๆ (ระบุในหมายเหตุ)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="EditReferentParentWrapper" style="display: none;">
                                            <div class="position-relative form-group">
                                                <label for="EditReferentParentId">ผู้แนะนำ:</label>
                                                <select class="form-control" id="EditReferentParentId" name="referent_parent_id">
                                                    <option value="">-- เลือกผู้แนะนำ --</option>
                                                    @foreach ($data4 ?? [] as $referent_parent)
                                                        <option value="{{ $referent_parent->id }}">
                                                            {{ $referent_parent->code }} {{ $referent_parent->name_parent }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative form-group">
                                                <label for="Remark">หมายเหตุ (Remark):</label>
                                                <textarea class="form-control" id="EditRemark" name="remark" rows="1" placeholder="ระบุรายละเอียดเพิ่มเติม"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Parent">Name Parent:</label>
                                                {{-- <input type="text" class="form-control" name="name_parent"
                                                    id="EditParent" value=""> --}}
                                                <select style="width: 100%;"
                                                    class="eparent select2 select2_single form-control" id="EditParent"
                                                    name="name_parent" multiple="multiple">
                                                    <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <option value="" selected>Select Parent</option>-->


                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Mobile">Mobile:</label>
                                                <input type="text" class="AddPhone form-control" name="mobile"
                                                    id="EditMobile" value="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Postcode">Postcode:</label>
                                                <input type="text" class="form-control" name="postcode"
                                                    id="EditPostcode" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Parent_id">Parent ID:</label>
                                            <input type="text" class="form-control" name="parentID" id="EditParentID"
                                                value="" hidden>
                                        </div>
                                    </div>



                                    {{-- <div class="form-row">
                                            <div class="col-md-6">
                                                <div class="position-relative form-group">
                                                    <label for="City">City:</label>
                                                    <input type="text" class="form-control" name="city"
                                                        id="EditCity" value="">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="position-relative form-group">
                                                    <label for="Province">Province:</label>
                                                    <input type="text" class="form-control" name="province"
                                                        id="EditProvince" value="">
                                                </div>
                                            </div>
                                        </div> --}}


                                    <div class="form-group">
                                        <label for="Name">Street Address:</label>
                                        <textarea class="form-control" name="address" id="EditAddress"></textarea>
                                    </div>
                                    {{-- <div class="form-group">
                                            <label for="Name">Street Address 2:</label>
                                            <textarea class="form-control" name="address2" id="EditAddress2"></textarea>
                                        </div> --}}
                                    <div class="form-group">
                                        <label for="Student">Student:</label><br>
                                        <select style="width: 100%;" class="select2 select2_multiple form-control"
                                            name="student" id="EditStudent" name="EditStudent[]" multiple="multiple">


                                        </select>
                                    </div>
                                    <div class="row imgs">

                                    </div>

                                    {{-- <div class="form-group">
                                                                                                                                                                                                                        <div class="imgs">

                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                    </div> --}}

                                    <div class="form-row form-focus" id="dropzone-photo">
                                        <div class="col-md-12 col-sm-12">
                                            <a id="dphoto"></a>
                                            <div class="position-relative form-group">
                                                <label class="form-label form-label-top form-label-auto"
                                                    for="att">อัพโหลดหลักฐานการชำระเงิน<span
                                                        class="form-required">*</span>
                                                    <div style="width: 280px"></div>
                                                </label>
                                                <input type="text" id="drop1" name="drop1"
                                                    class="form-control form_none" value="">
                                                <div class="dropzone" id="my-awesome-dropzone" style="font-size: 1.5em;">
                                                    <div class="fallback">

                                                    </div>
                                                </div>
                                                <label class="form-sub-label" style="min-height:13px"
                                                    aria-hidden="false">Receipt Photo</label>
                                                <div id="dropzone-att-error" class="form-error-message" role="alert">
                                                    <img src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                        height="10">
                                                    <span class="error-navigation-message">This field is
                                                        required.</span>
                                                    <div class="form-error-arrow">
                                                        <div class="form-error-arrow-inner"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropzonex dz-default dz-message" id="dropzone_preview"
                                        style="font-size: 1.5em;">
                                        <h3 class="dropzone-previews ui"></h3>
                                    </div>


                                </div>
                                <div class="tab-pane" id="tab-eg20-1" role="tabpanel">

                                    <div class="promotion-workflow-panel">
                                        <div class="promotion-workflow-header">
                                            <div>
                                                <div class="promotion-workflow-title">Campaign ส่วนลด</div>
                                                <div class="promotion-workflow-subtitle">Campaign ที่ตรงกับ Promotion และรายการเรียน (นักเรียนใหม่คำนวณแยกตามวิชา)</div>
                                            </div>
                                            <span class="promotion-status-pill">คำนวณอัตโนมัติ</span>
                                        </div>
                                        <input type="hidden" id="EditCalculatedPromotionCampaignId" value="">
                                        <input type="hidden" id="EditCalculatedPromotionTouched" value="0">
                                        <label class="promotion-context-toggle" for="EditIsNewStudentPromotionContext">
                                            <input type="checkbox" id="EditIsNewStudentPromotionContext" value="1" disabled>
                                            <span>นักเรียนใหม่</span>
                                        </label>
                                        <div id="EditCalculatedPromotionResult" class="small text-muted">
                                            <div class="promotion-state-message">Campaign เดิมจะถูกเก็บไว้จนกว่าจะเลือกใหม่</div>
                                        </div>
                                    </div>

                                    <table id="myTbl3"
                                        class="table table-striped table-bordered responsive-utilities jambo_table "
                                        width="90%" align="center">
                                        <thead>

                                            <tr class="headings">

                                                <th class="column-title"> NO.</th>
                                                <th class="column-title"> Student Name</th>
                                                <th class="column-title"> Course Type</th>
                                                <th class="column-title"> Subject</th>
                                                <th class="column-title"> Start Level</th>
                                                <th class="column-title"> Time</th>
                                                <th class="column-title"> Qty</th>
                                                <th class="column-title"> Book/</br>สื่อ</th>
                                                <th class="column-title"> Book/</br>สื่อ Price</th>
                                                <th class="column-title abc">Type</th>
                                                <th class="column-title"> Discount</th>
                                                <th class="column-title"> Price</th>
                                                <th class="column-title"> Period/รายละเอียดสื่อ</th>
                                                <th class="column-title"> Total</th>
                                                {{-- <th class="column-title"> </th> --}}
                                            </tr>

                                        </thead>

                                        <tbody>
                                            <tr class="firstTr3">
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown" name="flown"
                                                            class="form-control has-feedback-left" value="1"
                                                            required="required">

                                                    </div>
                                                </td>
                                                <td width="35%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                <td width="35%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>

                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>

                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                            </tr>
                                        </tbody>


                                    </table>
                                    <div class="col-md-12" align="right">

                                        <button type="button" id="addRow2"
                                            class="btn btn-sm btn-primary btnAddg"><i class="fa fa-plus"></i></button>
                                        <button type="button" id="removeRow2"
                                            class="btn btn-sm btn-danger btnRemoveg"><i
                                                class="fa fa-minus"></i></button>
                                    </div>

                                </div>
                            </div>
                            <!--tab-->
                        </div>

                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitEditForm">Update</button>
                        <button type="button" class="btn btn-danger modelClose"
                            data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Convert credit note  Modal -->
    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="ConvModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Create Credit Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <strong>Success!</strong> Credit Note was create successfully.
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="ConvModalBody">
                            <input type="hidden" id="conv_original_id" name="original_id">
                            <input type="hidden" id="conv_pid" name="pid">
                            <ul class="nav nav-tabs">
                                <li class="nav-item"><a data-bs-toggle="tab" href="#tab-cn-0"
                                        class="active nav-link">ข้อมูลลูกค้า</a></li>
                                <li class="nav-item"><a data-bs-toggle="tab" href="#tab-cn-1"
                                        class="nav-link">รายการในใบเสร็จ</a></li>

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab-cn-0" role="tabpanel">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="ReceiptNumber">Voucher Number:</label>
                                                <input type="text" class="form-control" name="conv_receipt_number"
                                                    id="conv_receipt_number" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative  form-group">
                                                <label for="ReceiptCNumber">Customer Code:</label>
                                                <input type="text" class="form-control" name="conv_customer_code"
                                                    id="conv_customer_code" value="" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="Submission Date">Submission Date:</label>
                                                <input type="text" class="AddDate form-control"
                                                    name="conv_submission_date" id="ConvSubmissiondate"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="Grade">Grade:</label>
                                                <input type="text" class="form-control" name="conv_grade"
                                                    id="ConvGrade" value="">
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-6">
                                            <div class="position-relative  form-group">
                                                <label for="Package">Package:</label>
                                                <input type="text" class="form-control" name="conv_package"
                                                    id="ConvPackage" value="">
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="firstname">First Name:</label>
                                                <input type="text" class="form-control" name="conv_firstname"
                                                    id="ConvFirstname" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative form-group">
                                                <label for="lastname">Last Name:</label>
                                                <input type="text" class="form-control" name="conv_lastname"
                                                    id="ConvLastname" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Parent">Name Parent:</label>
                                                <input type="text" class="form-control" name="conv_name_parent"
                                                    id="ConvParent" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Mobile">Mobile:</label>
                                                <input type="text" class="AddPhone form-control" name="conv_mobile"
                                                    id="ConvMobile" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Postcode">Postcode:</label>
                                                <input type="text" class="form-control" name="conv_postcode"
                                                    id="ConvPostcode" value="">
                                            </div>
                                        </div>
                                    </div>


                                    {{-- <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="Des">Description:</label>
                                            <textarea class="form-control" name="conv_drescription" id="ConvDes"></textarea>
                                        </div>
                                    </div> --}}

                                    <div class="form-group">
                                        <label for="Name">Street Address:</label>
                                        <textarea class="form-control" name="conv_address" id="ConvAddress"></textarea>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab-cn-1" role="tabpanel">

                                    <table id="myTbl2"
                                        class="table table-striped table-bordered responsive-utilities jambo_table "
                                        width="90%" align="center">
                                        <thead>

                                            <tr class="headings">

                                                <th class="column-title"> NO.</th>
                                                <th class="column-title"> Detail</th>
                                                <th class="column-title"> Qty</th>
                                                <th class="column-title"> Unit</th>
                                                <th class="column-title"> Unit Price</th>
                                                <th class="column-title"> Discount</th>
                                                {{-- <th class="column-title"> </th> --}}
                                            </tr>

                                        </thead>

                                        <tbody>
                                            <tr class="?????">
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value="1"
                                                            required="required">

                                                    </div>
                                                </td>
                                                <td width="35%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                <td width="10%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>

                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>
                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>

                                                <td width="15%">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="flown[]" name="flown[]"
                                                            class="form-control has-feedback-left" value=""
                                                            required="required">
                                                    </div>
                                                </td>



                                                {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                            </tr>
                                        </tbody>


                                    </table>
                                    <div class="col-md-12" align="right">

                                        <button type="button" id=""
                                            class="btn btn-sm btn-primary btnAddg2"><i class="fa fa-plus"></i></button>
                                        <button type="button" id="removeRow2"
                                            class="btn btn-sm btn-danger btnRemoveg2"><i
                                                class="fa fa-minus"></i></button>
                                    </div>

                                </div>
                            </div>

                            {{-- <div class="form-group">
                                <label for="File">File:</label>
                                <input type="text" class="form-control" id="EditFile" name="file"
                                    value="">
                            </div> --}}
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitConvForm">Create</button>
                        <button type="button" class="btn btn-danger modelClose"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('input[name="media_name"]').hide();
            $('input[name="media_price"]').hide();
            $('#type').prop("disabled", true);

            // let daterange = () => {
            $('.datePeriod').daterangepicker({
                // startDate: startDate,
                // endDate: endDate,
                locale: {
                    format: 'YYYY-MM'
                }
            });
            $('.more_reservation1').daterangepicker({
                locale: {
                    format: 'YYYY-MM'
                }
            });
            //     // Apply the custom date range filter on input change
            //     // $('#reservation').on('apply.daterangepicker', function() {
            //     //     table.draw();
            //     //     storeFieldValues();
            //     // });
            // }
            // daterange();

            // $('.reservation1').daterangepicker({
            //     locale: {
            //         format: 'YYYY-MM'
            //     }
            // });
            // daterange();

            //ดดดดด
            // $('.firstTr').ready(function() {

            //     var sub_id = $('#subject_id').val();
            //     if (sub_id == 3) {
            //         parent.find('#type').prop("disabled", false);
            //     } else {
            //         parent.find('#type').prop("disabled", true);
            //     }
            //     if (sub_id != '') {
            //         var _token = $('input[name="_token"]').val();
            //         $.ajax({
            //             method: "GET",
            //             url: "receipt_school/" + sub_id,
            //             success: function(res) {
            //                 parent.find('#level_id').html(res.html);

            //                 console.log('test : '+parent.find('#level_id').val());
            //             }
            //         });
            //     }

            // })

            // parent.find('#rrmedia_price').hide();
            // parent.find('#rrmedia_name').hide();


            $(".nav-tabs a").click(function() {
                $(this).tab('show');
            });
            $('.dropdown-toggle').dropdown();

            $(".select2_single").select2({
                maximumSelectionLength: 1,
                allowClear: true
            });

            $(".select2_multiple").select2({
                maximumSelectionLength: 2,
                //placeholder: "With Max Selection limit 4",
                allowClear: true
            });


            $(".AddDate").datepicker({
                dateFormat: "yy-mm-dd"
            });

            $(".AddMonth").datepicker({
                dateFormat: "yy-mm"
            });

            $(".FilterDate").datepicker({
                dateFormat: "dd/mm/yy"
            });

            $(".AddDate").inputmask("9999-99-99");
            $(".FilterDate").inputmask("99/99/9999");
            $(".AddMonth").inputmask("9999-99");
            $(".AddPhone").inputmask("(999) 999-9999");

            $(".delete_all_button").click(function() {
                var len = $('input[name="table_records[]"]:checked').length;
                if (len > 0) {
                    if (confirm("Click OK to Delete?")) {
                        $('form#delete_all').submit();
                    }
                } else {
                    alert("Please Select Record For Delete");
                }

            });

            $('#check-all').click(function() {
                $(':checkbox.flat').prop('checked', this.checked);
            });

            //$.noConflict();
            var token = ''
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function FormatPromotionAmount(amount) {
                var numericAmount = parseFloat(amount || 0);
                return numericAmount.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function EscapeHtml(text) {
                return $('<div>').text(text || '').html();
            }

            function BuildCreateReceiptItems() {
                var receiptItems = [];

                $('.firstTr').each(function(i, obj) {
                    if (i > 0) {
                        receiptItems.push({
                            student_id: $(obj).find('#std_id').val(),
                            subject_id: $(obj).find('#subject_id').val(),
                            course_type: $(obj).find('#course_type').val(),
                            month_count: $(obj).find('#qty').val(),
                            net_amount: $(obj).find('#pp').val(),
                            discount_amount: $(obj).find('#discount').val()
                        });
                    }
                });

                return receiptItems;
            }

            function BuildEditReceiptItems() {
                var receiptItems = [];

                $('.firstTr3').each(function(i, obj) {
                    receiptItems.push({
                        student_id: $(obj).find("select[name='student_id[]']").val(),
                        subject_id: $(obj).find("select[name='rrsub[]']").val(),
                        course_type: $(obj).find("select[name='course_type[]']").val(),
                        month_count: $(obj).find("select[name='rrqty[]']").val(),
                        net_amount: $(obj).find("input[name='rrtotal[]']").val(),
                        discount_amount: $(obj).find("input[name='rrdiscount[]']").val()
                    });
                });

                return receiptItems;
            }

            function BuildPromotionPreviewPayload(mode) {
                var isCreate = mode === 'create';
                var receiptItems = isCreate ? BuildCreateReceiptItems() : BuildEditReceiptItems();
                var source = isCreate ? $('#AddSource').val() : $('#EditSource').val();
                var referentParentId = isCreate ? $('#AddReferentParentId').val() : $('#EditReferentParentId').val();
                var promotionId = isCreate ? $('#AddPromotion').val() : $('#EditPromotion').val();

                return {
                    promotion_id: promotionId,
                    receipt_school_id: isCreate ? null : $('#id_parent').val(),
                    name_parent: isCreate ? ($('#AddParent').val() ? $('#AddParent').val()[0] : null) : ($('#EditParent').val() ? $('#EditParent').val()[0] : null),
                    source: source,
                    referent_parent_id: referentParentId,
                    receipt_items: receiptItems,
                    submission_date: isCreate ? $('#AddSubmissiondate').val() : $('#EditSubmissiondate').val(),
                    entrance_fee_amount: SumEntranceFeeAmount(mode)
                };
            }

            function SumEntranceFeeAmount(mode) {
                var selector = mode === 'create'
                    ? "#CreateModal input[name='entrance_fee']:checked"
                    : "#EditModal input[name='entrance_fee[]']:checked";

                var total = 0;
                $(selector).each(function() {
                    total += parseFloat($(this).val()) || 0;
                });

                return total;
            }

            function ApplyPromotionContextState(mode, context) {
                var target = mode === 'create' ? $('#CreateIsNewStudentPromotionContext') : $('#EditIsNewStudentPromotionContext');
                target.prop('checked', !!(context && context.is_new_student));
            }

            function SyncReferentParentVisibility(mode, shouldClear) {
                var isCreate = mode === 'create';
                var sourceTarget = isCreate ? $('#AddSource') : $('#EditSource');
                var referentWrapper = isCreate ? $('#AddReferentParentWrapper') : $('#EditReferentParentWrapper');
                var referentTarget = isCreate ? $('#AddReferentParentId') : $('#EditReferentParentId');
                var shouldShow = sourceTarget.val() === 'คนรู้จักแนะนำ';

                referentWrapper.toggle(shouldShow);

                if (!shouldShow && shouldClear) {
                    referentTarget.val('');
                }
            }

            window.CalculatedPromotionOptions = {
                create: {},
                edit: {}
            };
            window.AppliedCalculatedPromotionIds = {
                create: null,
                edit: null
            };

            function RenderCalculatedPromotionOptions(mode, promotions) {
                var resultTarget = mode === 'create' ? $('#CreateCalculatedPromotionResult') : $('#EditCalculatedPromotionResult');
                var preSelectedIds = (mode === 'edit') ? (window.EditCurrentCampaignIds || []) : [];
                window.CalculatedPromotionOptions[mode] = {};

                if (!promotions || promotions.length === 0) {
                    resultTarget.html('<div class="promotion-state-message">ยังไม่มี Campaign ที่เข้าเงื่อนไขจากรายการเรียนตอนนี้</div>');
                    SyncCalculatedPromotionHidden(mode);
                    return;
                }

                var html = '<div class="campaign-option-list">';
                $.each(promotions, function(index, promotion) {
                    window.CalculatedPromotionOptions[mode][promotion.promotion_campaign_id] = promotion;
                    var inputId = mode + '_calculated_promotion_' + promotion.promotion_campaign_id;
                    var isPreSelected = preSelectedIds.indexOf(parseInt(promotion.promotion_campaign_id)) !== -1;
                    var categories = (promotion.reward_categories || []).join(',');
                    html += '<label class="campaign-option-card' + (isPreSelected ? ' is-selected' : '') + '" for="' + inputId + '">';
                    html += '<div class="campaign-option-main">';
                    html += '<div>';
                    html += '<input class="mr-2 calculated-promotion-option" type="radio" name="' + mode + '_calculated_promotion" id="' + inputId + '" value="' + promotion.promotion_campaign_id + '" data-categories="' + EscapeHtml(categories) + '"' + (isPreSelected ? ' checked' : '') + '>';
                    html += '<span class="campaign-option-name">' + EscapeHtml(promotion.name) + '</span>';
                    html += '<div><span class="campaign-option-type">' + EscapeHtml(BuildCampaignTypeLabel(promotion.promotion_type)) + '</span></div>';
                    html += '</div>';
                    var rewardText = promotion.reward_summary
                        ? promotion.reward_summary
                        : (promotion.discount_amount > 0 ? 'ลด ' + FormatPromotionAmount(promotion.discount_amount) + ' บาท' : 'รับสิทธิ์โปรโมชั่น');
                    html += '<div class="campaign-option-discount">' + EscapeHtml(rewardText) + '</div>';
                    html += '</div>';
                    html += '</label>';
                });
                html += '</div>';
                html += '<button type="button" class="btn btn-sm btn-link campaign-clear-action clear-calculated-promotion" data-mode="' + mode + '">ล้าง Campaign ที่เลือก</button>';

                resultTarget.html(html);

                SyncCalculatedPromotionHidden(mode);
            }

            // Enforce: campaigns sharing a reward category (เช่น ส่วนลดเงินสด) cannot be selected together.
            function EnforceCampaignCategoryConstraint(mode, changedInput) {
                if (!changedInput.is(':checked')) {
                    return;
                }

                var changedCategories = (changedInput.data('categories') || '').toString().split(',').filter(Boolean);
                if (changedCategories.length === 0) {
                    return;
                }

                $("input[name='" + mode + "_calculated_promotion']:checked").each(function() {
                    if (this === changedInput[0]) {
                        return;
                    }

                    var otherCategories = ($(this).data('categories') || '').toString().split(',').filter(Boolean);
                    var hasOverlap = otherCategories.some(function(category) {
                        return changedCategories.indexOf(category) !== -1;
                    });

                    if (hasOverlap) {
                        $(this).prop('checked', false);
                        $(this).closest('.campaign-option-card').removeClass('is-selected');
                    }
                });
            }

            // Mirror checked campaign ids into the hidden field as a comma-separated list.
            function SyncCalculatedPromotionHidden(mode) {
                var hiddenTarget = mode === 'create' ? $('#CreateCalculatedPromotionCampaignId') : $('#EditCalculatedPromotionCampaignId');
                var ids = [];
                $("input[name='" + mode + "_calculated_promotion']:checked").each(function() {
                    ids.push($(this).val());
                    $(this).closest('.campaign-option-card').addClass('is-selected');
                });
                hiddenTarget.val(ids.join(','));
            }

            function GetSelectedCalculatedPromotion(mode) {
                var selectedIds = GetSelectedCalculatedPromotionIds(mode);
                if (selectedIds.length === 0) {
                    return null;
                }

                return window.CalculatedPromotionOptions[mode][selectedIds[0]] || null;
            }

            function GetSelectedCalculatedPromotionIds(mode) {
                var hiddenTarget = mode === 'create' ? $('#CreateCalculatedPromotionCampaignId') : $('#EditCalculatedPromotionCampaignId');
                var value = (hiddenTarget.val() || '').toString();
                return value === '' ? [] : value.split(',');
            }

            function BuildCampaignTypeLabel(promotionType) {
                if (promotionType === 'TWO_SUBJECTS') {
                    return 'Math + English';
                }

                if (promotionType === 'REFERRAL') {
                    return 'เพื่อนแนะนำเพื่อน';
                }

                if (promotionType === 'NEW_STUDENT_MULTI_COURSE') {
                    return 'นักเรียนใหม่';
                }

                if (promotionType === 'NEW_STUDENT_FREE_ENTRANCE_GIFT'
                    || promotionType === 'NEW_STUDENT_FREE_ENTRANCE'
                    || promotionType === 'NEW_STUDENT_GIFT') {
                    return 'นักเรียนใหม่';
                }

                return 'Custom';
            }

            function ResetCalculatedPromotionPanel(mode) {
                if (mode === 'create') {
                    $('#CreateCalculatedPromotionCampaignId').val('');
                    window.AppliedCalculatedPromotionIds.create = null;
                    $('#CreateIsNewStudentPromotionContext').prop('checked', false);
                    SyncReferentParentVisibility('create', true);
                    $('#CreateCalculatedPromotionResult').html('<div class="promotion-state-message">เลือก Promotion เพื่อดู Campaign ที่ใช้ได้</div>');
                    $("input[name='create_calculated_promotion']").prop('checked', false);
                    return;
                }

                $('#EditCalculatedPromotionCampaignId').val('');
                window.AppliedCalculatedPromotionIds.edit = null;
                $('#EditCalculatedPromotionTouched').val('0');
                $('#EditIsNewStudentPromotionContext').prop('checked', false);
                SyncReferentParentVisibility('edit', false);
                $('#EditCalculatedPromotionResult').html('<div class="promotion-state-message">Campaign เดิมจะถูกเก็บไว้จนกว่าจะเลือกใหม่</div>');
                $("input[name='edit_calculated_promotion']").prop('checked', false);
                window.EditCurrentCampaignIds = [];
                window.EditCurrentCampaignId = null;
            }

            $('#CreateModal').on('hidden.bs.modal', function() {
                ResetCalculatedPromotionPanel('create');
            });

            $('#EditModal').on('show.bs.modal', function() {
                ResetCalculatedPromotionPanel('edit');
            });

            var calculatedPromotionPreviewTimers = {
                create: null,
                edit: null
            };

            function PreviewCalculatedPromotions(mode, markTouched) {
                var isCreate = mode === 'create';
                var resultTarget = isCreate ? $('#CreateCalculatedPromotionResult') : $('#EditCalculatedPromotionResult');
                var selectedPromotionId = isCreate ? $('#AddPromotion').val() : $('#EditPromotion').val();

                if (!selectedPromotionId) {
                    if (isCreate) {
                        $('#CreateCalculatedPromotionCampaignId').val('');
                        $("input[name='create_calculated_promotion']").prop('checked', false);
                    } else {
                        $('#EditCalculatedPromotionCampaignId').val('');
                        $("input[name='edit_calculated_promotion']").prop('checked', false);
                    }
                    resultTarget.html('<div class="promotion-state-message">เลือก Promotion เพื่อดู Campaign ที่ใช้ได้</div>');
                    return;
                }

                if (!isCreate && markTouched) {
                    $('#EditCalculatedPromotionTouched').val('1');
                }

                resultTarget.html('<div class="promotion-state-message is-loading">กำลังคำนวณ Campaign ที่เข้าเงื่อนไข...</div>');

                $.ajax({
                    url: "{{ route('receipt_school.promotions.preview') }}",
                    method: 'POST',
                    data: BuildPromotionPreviewPayload(mode),
                    success: function(result) {
                        if (result.errors) {
                            resultTarget.html('<div class="promotion-state-message is-error">' + $.map(result.errors, EscapeHtml).join('<br>') + '</div>');
                            return;
                        }

                        ApplyPromotionContextState(mode, result.promotion_context || {});
                        RenderCalculatedPromotionOptions(mode, result.eligible_promotions);
                    },
                    error: function() {
                        resultTarget.html('<div class="promotion-state-message is-error">คำนวณ Campaign ไม่สำเร็จ กรุณาตรวจข้อมูลแล้วลองอีกครั้ง</div>');
                    }
                });
            }

            function ScheduleCalculatedPromotionPreview(mode, markTouched) {
                clearTimeout(calculatedPromotionPreviewTimers[mode]);
                calculatedPromotionPreviewTimers[mode] = setTimeout(function() {
                    PreviewCalculatedPromotions(mode, markTouched);
                }, 350);
            }

            window.ScheduleCalculatedPromotionPreview = ScheduleCalculatedPromotionPreview;

            $(document).on('change', '#AddPromotion, #AddReferentParentId, #CreateIsNewStudentPromotionContext, #AddSubmissiondate', function() {
                window.ScheduleCalculatedPromotionPreview('create', false);
            });

            $(document).on('change', '#EditPromotion, #EditReferentParentId, #EditIsNewStudentPromotionContext, #EditSubmissiondate', function() {
                window.ScheduleCalculatedPromotionPreview('edit', true);
            });

            $(document).on('change', '#AddSource', function() {
                SyncReferentParentVisibility('create', true);
                window.ScheduleCalculatedPromotionPreview('create', false);
            });

            $(document).on('change', '#EditSource', function() {
                SyncReferentParentVisibility('edit', true);
                window.ScheduleCalculatedPromotionPreview('edit', true);
            });

            $(document).on('change keyup', "#CreateModal select[name='subject_id'], #CreateModal select[name='qty'], #CreateModal select[name='course_type']", function() {
                window.ScheduleCalculatedPromotionPreview('create', false);
            });

            $(document).on('change keyup', "#EditModal select[name='rrsub[]'], #EditModal select[name='rrqty[]'], #EditModal select[name='course_type[]'], #EditModal input[name='rrtotal[]']", function() {
                window.ScheduleCalculatedPromotionPreview('edit', true);
            });

            $(document).on('change', "input[name='create_calculated_promotion']", function() {
                EnforceCampaignCategoryConstraint('create', $(this));
                $("input[name='create_calculated_promotion']").closest('.campaign-option-card').removeClass('is-selected');
                SyncCalculatedPromotionHidden('create');
            });

            $(document).on('change', "input[name='edit_calculated_promotion']", function() {
                $('#EditCalculatedPromotionTouched').val('1');
                EnforceCampaignCategoryConstraint('edit', $(this));
                $("input[name='edit_calculated_promotion']").closest('.campaign-option-card').removeClass('is-selected');
                SyncCalculatedPromotionHidden('edit');
            });

            $(document).on('click', '.clear-calculated-promotion', function() {
                var mode = $(this).data('mode');

                if (mode === 'create') {
                    $("input[name='create_calculated_promotion']").prop('checked', false);
                    $("input[name='create_calculated_promotion']").closest('.campaign-option-card').removeClass('is-selected');
                    SyncCalculatedPromotionHidden('create');
                    window.AppliedCalculatedPromotionIds.create = null;
                } else {
                    $('#EditCalculatedPromotionTouched').val('1');
                    $("input[name='edit_calculated_promotion']").prop('checked', false);
                    $("input[name='edit_calculated_promotion']").closest('.campaign-option-card').removeClass('is-selected');
                    SyncCalculatedPromotionHidden('edit');
                    window.AppliedCalculatedPromotionIds.edit = null;
                }
            });

            // แปลงวันที่จากรูปแบบ DD/MM/YYYY (ที่ผู้ใช้กรอก) เป็น YYYY-MM-DD ที่ใช้เทียบกับ DB
            function toIsoDate(value) {
                if (!value) {
                    return '';
                }
                var parts = value.split('/');
                if (parts.length !== 3) {
                    return '';
                }
                var day = parts[0],
                    month = parts[1],
                    year = parts[2];
                if (day.length !== 2 || month.length !== 2 || year.length !== 4) {
                    return '';
                }
                return year + '-' + month + '-' + day;
            }

            var table = $('#Listview').DataTable({

                ajax: {
                    url: '',
                    data: function(d) {
                        d.type_filter = $('#type_filter').val();
                        d.promotion_filter = $('#filter_promotion').val();
                        d.payment_filter = $('#filter_payment').val();
                        d.source_filter = $('#filter_source').val();
                        d.date_start = toIsoDate($('#filter_date_start').val());
                        d.date_end = toIsoDate($('#filter_date_end').val());
                    }
                },
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: '<div class="spinner"></div>'
                },
                aaSorting: [
                    [0, "desc"]
                ],
                iDisplayLength: 10,
                lengthMenu: [10, 20, 50, 75, 100],
                stateSave: true,
                sPaginationType: "full_numbers",
                dom: 'T<"clear">lfrtip',
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'type2',
                        name: 'type2'
                    },
                    {
                        data: 'promotion_name',
                        name: 'promotion_name'
                    },
                    {
                        data: 'submission_date',
                        name: 'submission_date'
                    },
                    {
                        data: 'receipt_number',
                        name: 'receipt_number'
                    },
                    {
                        data: 'name_parent',
                        name: 'name_parent'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'street_address',
                        name: 'street_address'
                    },
                    {
                        data: 'postcode',
                        name: 'postcode'
                    },
                    {
                        data: 'pid',
                        name: 'pid'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function(data, type, full, meta) {
                            var btn = '<span class="text-nowrap">' + data + '</span>';
                            btn += '<button type="button" class="btn btn-sm btn-warning btn-conv" data-id="' + full.id + '" title="Credit Note"><i class="fa fa-retweet"></i></button>';
                            return btn;
                        }
                    },
                ],
                createdRow: function(row, data, dataIndex) {
                    var firstTwoDigits = data.receipt_number.substring(0, 2);
                    var type = data.type2;
                    console.log('type:' + type);

                    if (firstTwoDigits === '01' || firstTwoDigits === '71') {
                         // $(row).find('td:eq(3)').addClass('bg-danger');
                         // $(row).addClass('bg-cyan');
                         $(row).addClass('bg-red');
                    } else if (firstTwoDigits === '03' || firstTwoDigits === '73') {
                         // $(row).find('td:eq(3)').addClass('bg-danger');
                         $(row).addClass('bg-cyan');
                         // $(row).addClass('bg-red');
                    } else if (firstTwoDigits === 'CN') {
                        $(row).addClass('bg-warning');
                    }
                    // else if (type === '9') {
                    //     // $(row).find('td:eq(3)').addClass('bg-danger');
                    //     $(row).addClass('bg-warning');
                    //     // $(row).addClass('bg-red');
                    // }
                }
            });

            $('#filter_apply').on('click', function() {
                table.draw();
            });

            $('#filter_clear').on('click', function() {
                $('#type_filter').val('');
                $('#filter_promotion').val('');
                $('#filter_payment').val('');
                $('#filter_source').val('');
                $('#filter_date_start').val('');
                $('#filter_date_end').val('');
                table.draw();
            });

            $('.dropdown-menu li #printr1').on('click', function(e) {
                //console.log("TEST");
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('.dropdown-toggle').dropdown('hide');
                $('#PrintDayModal').modal('show');
            });

            $('.dropdown-menu li #printr2').on('click', function(e) {
                //console.log("TEST");
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('.dropdown-toggle').dropdown('hide');
                $('#PrintMonthModal').modal('show');
            });

            $(document).on('click', '#CreateButton', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('#CreateModal').modal('show');
            });

            $(document).on('click', '#SubmitPrintDayForm', function(e) {
                e.preventDefault();
                //$('#loder_bar').show();
                //$.blockUI({
                //    message: $("#loder_bar")
                //});
                //$("#PrintDayForm").submit();

            });

            $(document).on('click', '#SubmitImportForm', function(e) {
                e.preventDefault();
                //$('#loder_bar').show();
                $.blockUI({
                    message: $("#loder_bar")
                });
                $("#importForm").submit();

            });
            
            $(document).on("click", ".btn-delete", function(e) {
                e.preventDefault(); // Prevent default action

                let id = $(this).data("id"); // Get the record ID
                let url = "/receipt_school/destroy/" + id; // Match your Laravel route

                if (confirm("Are you sure you want to delete this receipt?")) {
                    $.ajax({
                        url: url,
                        type: "DELETE",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr("content") // CSRF token
                        },
                        success: function(response) {
                            alert(response.success); // Show success message
                            location.reload(); // Reload to update UI
                        },
                        error: function(xhr) {
                            alert("Error deleting data!"); // Handle errors
                        }
                    });
                }
            });



            $(document).on('click', '#ImportButton', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('#ImportModal').modal('show');
            });

            let id;
            let id2;
            $(document).on('click', '#getDeleteData', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                id = $(this).data('id');
                id2 = $(this).data('id2');
                $.ajax({
                    url: "receipt_school/delete/" + id + "/" + id2,
                    method: 'PUT',
                    // async : false,
                    success: function(res) {
                        $('.imgs').html(res.imgs);

                    }
                })
            })

            $(document).on('click', '#getEditData', function(e) {
                e.preventDefault();
                // var parent = $(this);
                // //resize size bar
                // parent.find('#rrmedia_price').hide();
                //     parent.find('#rrmedia_name').hide();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                id = $(this).data('id');
                $.ajax({
                    url: "receipt_school/" + id + "/0",
                    method: 'GET',
                    success: function(res) {
                        // console.log(res);
                        //$('#EditModalBody').html(res.html);
                        //console.log(res.data_html);
                        $('#id_parent').val(res.data.id);
                        $('#receipt_number').val(res.data.receipt_number);
                        $('#customer_code').val(res.data2.code);
                        $('#EditSubmissiondate').val(res.data.submission_date);
                        $('#EditPayment').html(res.payment)
                        $('#EditPromotion').html(res.promotion_options);
                        $('#EditSource').val(res.data.source);
                        $('#EditReferentParentId').val(res.data.referent_parent_id);
                        SyncReferentParentVisibility('edit', false);
                        $('#EditRemark').val(res.data.remark);
                        //$('#EditPackage').val(res.data.package);
                        $('#EditAddress').val(res.data.street_address);
                        //$('#EditAddress2').val(res.data.street_address2);
                        //$('#EditCity').val(res.data.city);
                        //$('#EditProvince').val(res.data.province);
                        $('#EditPostcode').val(res.data.postcode);
                        $('#EditParent').html(res.select_list_parent);
                        $('#EditMobile').val(res.data.mobile);
                        $('#EditParentID').val(res.data2.id);
                        $('#EditStudent').html(res.student)
                        $('.imgs').html(res.imgs);
                        
                        // Set is_vat checkbox
                        if (res.data.is_vat == 1) {
                            $('#EditIsVat').prop('checked', true);
                        } else {
                            $('#EditIsVat').prop('checked', false);
                        }

                        $('#myTbl3 tbody').html(res.data_html);
                        //append
                        //$('#EditFile').val(res.data.file_att);
                        $('#EditModal').modal('show');
                        window.EditCurrentCampaignIds = (res.current_campaign_ids || []).map(function(id) {
                            return parseInt(id);
                        });
                        window.EditCurrentCampaignId = window.EditCurrentCampaignIds.length ? window.EditCurrentCampaignIds[0] : null;
                        if (window.EditCurrentCampaignIds.length > 1) {
                            $('#EditCalculatedPromotionTouched').val('1');
                        }
                        PreviewCalculatedPromotions('edit', false);
                        // let id
                        // id = $(this).data('id');



                        var rrmedia_price = document.getElementsByClassName('rrmedia_price');
                        var rrbookprice = document.getElementsByClassName('rrbookprice');
                        var rrmedia_name = document.getElementsByClassName('rrmedia_name');
                        var rrperiod = document.getElementsByClassName('rrperiod');
                        var rrcourse_type = document.getElementsByClassName('rrcourse_type');

                        for (var i = 0; i < rrcourse_type.length; i++) {
                            var currentValue = rrcourse_type[i].value;

                            if (rrcourse_type[i].value == 3) {
                                rrbookprice[i].style.display = 'none';
                                rrperiod[i].style.display = 'none';

                                // rrbookprice[i].style.display = 'none';
                            } else {
                                rrmedia_price[i].style.display = 'none';
                                rrmedia_name[i].style.display = 'none';

                            }

                        }
                        // $(document).ready(function() {
                        //     var parent = $('#EditParent').val();
                        //     // console.log('test ready : ' + parent);

                        //     $.ajax({
                        //         method: "GET",
                        //         url: "receipt_school/find/" + parent,
                        //         success: function(res) {

                        //             $('.stdrec').html(res.html5);
                        //             // console.log('patent : '+parent);

                        //         }
                        //     });
                        //     // var testArray = $('#student_id\\[\\]').val();
                        //     // console.log('test_std :', testArray);

                        // });
                        // for (var i = 0; i < rrmedia_name.length; i++) {
                        //     var currentValue = rrmedia_name[i].value;

                        //     if (!rrmedia_name[i].value) {
                        //         rrmedia_name[i].style.display = 'none';
                        //         // rrperiod[i].style.display = 'none';
                        //     }else{
                        //         rrperiod[i].style.display = 'none';
                        //     }
                        //     console.log('Value of rrmedia_name element ' + i + ': ' +
                        //         currentValue);
                        // }

                        // console.log(id);
                        // parent.find('#rrmedia_price').prop("disabled", true);
                        // parent.find('#rrmedia_name').prop("disabled", true);
                        // parent.find('#rrmedia_price').hide();
                        // parent.find('#rrmedia_name').hide();

                    }
                });

            });

            let sub_id;
            $(".parent").change(function() {
                parent = $('#AddParent').val();
                console.log(parent);
                $.ajax({
                    method: "GET",
                    url: "receipt_school/find/" + parent,
                    success: function(res) {
                        $('#AddStudent').html(res.html);
                        $('#AddMobile').val(res.html2);
                        $('#AddAddress').val(res.html3);
                        $('#AddPostcode').val(res.html4);
                        $('.stdrec').html(res.html5);

                    }
                });
            })

            $(".eparent").change(function() {
                var parent = $('#EditParent').val();
                console.log(parent);
                $.ajax({
                    method: "GET",
                    url: "receipt_school/find/" + parent,
                    success: function(res) {
                        $('#EditStudent').html(res.html);
                        $('#EditMobile').val(res.html2);
                        $('#EditAddress').val(res.html3);
                        $('#EditPostcode').val(res.html4);
                        $('.stdrec').html(res.html5);

                    }
                });
            });

            //อยู่



            $(document).on('change', '#rrsub', function(e) {
                // console.log($(this).parent().parent().parent().html())
                // console.log($(this).parent().parent().parent().find('#type').html())
                // $(this).parent().parent().parent().find('#discount').val(new Date().toDateString())
                // $('#discount').val(new Date().toDateString())
                var parent = $(this).parent().parent().parent();
                sub_id = $(this).parent().parent().parent().find('#rrsub').val();
                course_type = $(this).parent().parent().parent().find('#course_type').val();
                total = $(this).parent().parent().parent().find('#rrtotal').val();
                book = $(this).parent().parent().parent().find('#rrbook').val();
                bookprice = $(this).parent().parent().parent().find('#rrbookprice').val();
                type = $(this).parent().parent().parent().find('#rrtype').val();
                qty = $(this).parent().parent().parent().find('#rrqty').val();
                price = $(this).parent().parent().parent().find('#rrprice').val();
                week = $(this).parent().parent().parent().find('#rrweek').val("");
                discount = $(this).parent().parent().parent().find('#rrdiscount').val();
                weekh = $(this).parent().parent().parent().find('.weekh').hide();
                qtyh = $(this).parent().parent().parent().find('.qtyh').hide();
                priceh = $(this).parent().parent().parent().find('.priceh').hide();

                //alert(course_type);
                // console.log(sub_id);
                if (sub_id == 3) {
                    // parent.find('.abc').prop( "disabled", false );
                    parent.find('#rrtype').prop("disabled", false);

                } else {
                    // parent.find('.abc').prop( "disabled", true );
                    parent.find('#rrtype').prop("disabled", true);
                }


                if (week == 4) {
                    // parent.find('.abc').prop( "disabled", false );
                    parent.find('#rrqty').prop("disabled", false);
                    parent.find('#rrqty').val(1);


                } else {
                    // parent.find('.abc').prop( "disabled", true );
                    parent.find('#rrqty').prop("disabled", true);
                    parent.find('#rrqty').val(1);
                }


                $.ajax({
                    method: "GET",
                    async: false,
                    url: "receipt_school/" + sub_id,
                    success: function(res) {
                        parent.find('#rrlevel').html(res.html);
                        // console.log(res);

                    }
                });
            })


            //course type
            $(document).on('change', '#course_type', function(e) {
                var parent = $(this).parent().parent().parent();
                course_type = $(this).parent().parent().parent().find('#course_type').val();
                //alert(course_type);
                //bookonly
                if (course_type == 2) {
                    parent.find('#qty').prop("disabled", true);
                    parent.find('#week').prop("disabled", true);
                    parent.find('#period').prop("disabled", true);
                    parent.find('#book_price').prop("disabled", true);
                    parent.find('#qty').prop("disabled", true);
                    parent.find('#week').prop("disabled", true);
                    //parent.find('#price').prop("disabled", true);
                    parent.find('#price').prop("disabled", false);
                    parent.find('#period').prop("disabled", false);
                    parent.find('#book_price').prop("disabled", false);
                } else {
                    parent.find('#price').prop("disabled", false);
                    parent.find('#qty').prop("disabled", false);
                    parent.find('#week').prop("disabled", false);
                    parent.find('#period').prop("disabled", false);
                    parent.find('#book_price').prop("disabled", true);
                }

            });

            $(document).on('change', '#week', function(e) {
                // console.log($(this).parent().parent().parent().html())
                // console.log($(this).parent().parent().parent().find('#type').html())
                // $('#discount').val(new Date().toDateString())
                var parent = $(this).parent().parent().parent();
                week = $(this).parent().parent().parent().find('#week').val();
                course_type = $(this).parent().parent().parent().find('#course_type').val();
                //alert(course_type);
                //bookonly
                if (course_type == 2) {
                    parent.find('#qty').prop("disabled", true);
                    parent.find('#week').prop("disabled", true);
                    parent.find('#period').prop("disabled", true);
                    parent.find('#price').prop("disabled", false);
                    parent.find('#book_price').prop("disabled", true);
                } else if (course_type == 3) {
                    parent.find('#qty').prop("disabled", true);
                    parent.find('#week').prop("disabled", true);
                    //parent.find('#price').prop("disabled", true);
                    parent.find('#period').prop("disabled", false);
                    parent.find('#book_price').prop("disabled", false);
                } else {
                    parent.find('#qty').prop("disabled", false);
                    parent.find('#book_price').prop("disabled", true);
                    parent.find('#price').prop("disabled", false);
                    if (week == 4) {
                        // parent.find('.abc').prop( "disabled", false );
                        parent.find('#qty').prop("disabled", false);

                    } else {
                        // parent.find('.abc').prop( "disabled", true );
                        parent.find('#qty').prop("disabled", true);
                        parent.find('#qty').val(1);
                    }
                }
            }) // $(this).parent().parent().parent().find('#discount').val(new Date().toDateString())

            $(".aaa").change(function() {
                var parent = $(this).parent().parent().parent();
                sub_id = $(this).parent().parent().parent().find('#subject_id').val();
                if (sub_id == 3) {
                    parent.find('#type').prop("disabled", false);
                } else {
                    parent.find('#type').prop("disabled", true);
                }
                $.ajax({
                    method: "GET",
                    url: "receipt_school/" + sub_id,
                    success: function(res) {
                        parent.find('#level_id').html(res.html);
                    }
                });


            })


            $(document).on('change', '#rrweek', function(e) {
                // console.log($(this).parent().parent().parent().html())
                // console.log($(this).parent().parent().parent().find('#type').html())
                // $('#discount').val(new Date().toDateString())
                var parent = $(this).parent().parent().parent();
                var week = $(this).parent().parent().parent().find('#rrweek').val();
                var course_type = $(this).parent().parent().parent().find('#course_type').val();
                //alert(course_type);
                //bookonly
                if (course_type == 2) {
                    parent.find('#rrqty').prop("disabled", true);
                    parent.find('#rrweek').prop("disabled", true);
                    parent.find('#rrperiod').prop("disabled", true);
                    parent.find('#rrprice').prop("disabled", false);
                    parent.find('#rrbook_price').prop("disabled", true);

                } else if (course_type == 3) {

                    parent.find('#rrqty').prop("disabled", true);
                    parent.find('#rrweek').prop("disabled", true);
                    //parent.find('#price').prop("disabled", true);
                    parent.find('#rrperiod').prop("disabled", false);
                    parent.find('#rrbook_price').prop("disabled", false);
                } else {

                    parent.find('#rrqty').prop("disabled", false);
                    parent.find('#rrbook_price').prop("disabled", true);
                    parent.find('#rrprice').prop("disabled", false);
                    if (week == 4) {
                        // parent.find('.abc').prop( "disabled", false );
                        parent.find('#rrqty').prop("disabled", false);
                        parent.find('#rrqty').val(1);
                    } else {
                        // parent.find('.abc').prop( "disabled", true );
                        parent.find('#rrqty').prop("disabled", true);
                        parent.find('#rrqty').val(1);
                    }
                }
            })

            $(document).on('change', '#rrbook', function(e) {
                var parent = $(this).parent().parent().parent();
                var course_type = parent.find('#course_type').val();
                var sub_id = parent.find('#rrsub').val();
                var level_id = parent.find('#rrlevel').val();
                var book = parent.find('#rrbook').val();
                var week = parent.find('#rrweek').val();
                // console.log(book);
                if (course_type != 3) {
                    $.ajax({
                        type: "GET",
                        async: false,
                        dataType: 'JSON',
                        // url: "receipt_school/" + sub_id + "/" + level_id + "/" + book + "/" + course_type ,
                        url: "receipt_school/" + sub_id + "/" + level_id + "/" + book + "/" + week +
                            "/" + course_type,
                        success: function(res) {
                            parent.find('#rrbookprice').val(res.bp);
                            // $('.pp').html(res.html);
                            //console.log(result);
                            // $("#AddLevel").html(result.html);
                            // $("#AddBook").html(result.html2);
                        }
                    });
                }

            })
            //เทษ

            // parent.find('#rrmedia_price').prop("disabled", true);
            // parent.find('#rrmedia_name').prop("disabled", true);
            // parent.find('#rrmedia_price').hide();
            // parent.find('#rrmedia_name').hide();
            var entrance_fee = 0;

            $(document).on('change', '.firstTr3', function(e) {

                var parent = $(this);
                var sub_id = parent.find('#rrsub').val();
                // var sub_id = parent.find('#rrsub').val();
                var course_type = parent.find('#course_type').val();
                var level_id = parent.find('#rrlevel').val();
                var week = parent.find('#rrweek').val();
                var price = parent.find('#rrprice').val();
                var qty = parent.find('#rrqty').val();
                var discount = parent.find('#rrdiscount').val();
                var book_price = parent.find('#rrbookprice').val();
                var type = parent.find('#rrtype').val();
                var media_price = parent.find('#rrmedia_price').val();
                var media_name = parent.find('#rrmedia_name').val();
                var entrace_edit = $('#entrace_edit').val();
                var entrace_edit_0 = parent.find('#entrace_edit_0').val();

                if (entrace_edit_0 == 0) {
                    entrance_fee = 0;
                }
                if ($('#entrance_fee_val_1').is(':checked')) {
                    entrance_fee = $('#entrance_fee_val_1').val();
                } else {
                    entrance_fee = 0;
                }

                console.log('entrace_edit : ' + entrace_edit);
                console.log('entrance_fee : ' + entrance_fee);

                if (course_type == 2) {
                    parent.find('#rrqty').prop("disabled", true);
                    parent.find('#rrweek').prop("disabled", true);
                    parent.find('#rrperiod').prop("disabled", true);
                    parent.find('#rrmedia_price').prop("disabled", true);
                    parent.find('#rrmedia_name').prop("disabled", true);
                    parent.find('#rrmedia_price').hide();
                    parent.find('#rrmedia_name').hide();
                    parent.find('#entrance_fee_1').hide();
                    parent.find('#rrperiod').show();
                    parent.find('#rrbookprice').show();

                } else if (course_type == 3) {

                    parent.find('#rrqty').prop("disabled", true);
                    parent.find('#rrweek').prop("disabled", true);
                    parent.find('#rrperiod').prop("disabled", true);
                    parent.find('#rrbookprice').prop("disabled", true);
                    parent.find('#rrmedia_price').prop("disabled", false);
                    parent.find('#rrmedia_name').prop("disabled", false);
                    parent.find('#rrperiod').hide();
                    parent.find('#rrbookprice').hide();
                    parent.find('#entrance_fee_1').hide();
                    parent.find('#rrmedia_price').show();
                    parent.find('#rrmedia_name').show();
                } else {
                    if (week == 4) {
                        parent.find('#rrqty').prop("disabled", false);
                    } else {
                        parent.find('#rrqty').prop("disabled", true);
                    }
                    parent.find('#rrmedia_price').prop("disabled", true);
                    parent.find('#rrmedia_name').prop("disabled", true);
                    parent.find('#rrmedia_price').hide();
                    parent.find('#rrmedia_name').hide();
                    parent.find('#rrweek').prop("disabled", false);
                    parent.find('#rrperiod').prop("disabled", false);
                    parent.find('#rrperiod').show();
                    parent.find('#rrbookprice').show();
                    parent.find('#entrance_fee_1').show();

                }

                if (course_type == 2) {
                    week = 0;
                    qty = 0;
                    //price = 0;
                }
                if (course_type == 3) {
                    let book = parent.find('#rrbook').val();
                    let ssp = media_price * book;
                    parent.find('#rrtotal').val(ssp);
                } else {

                    // if (entrace_edit > 0) {
                    //     entrance_fee = 0;
                    // }
                    if (sub_id != "" && level_id != "" && price != "" && discount != "" && book_price !=
                        "" &&
                        type != "" && course_type != "") {
                        $.ajax({
                            type: "GET",
                            dataType: 'JSON',
                            url: "receipt_school/" + sub_id + "/" + level_id + "/" + week + "/" +
                                price + "/" + qty + "/" + discount + "/" + book_price + "/" + type +
                                "/" + course_type + "/" + entrance_fee,
                            success: function(res) {
                                parent.find('#rrtotal').val(res.outputsum);
                                parent.find('#entrace_edit').val(res.entrace_edit);
                                //console.log(result);
                                // $("#AddLevel").html(result.html);
                                // $("#AddBook").html(result.html2);
                            }
                        });
                    }
                }


            })

            $(document).on('change', '#book', function(e) {
                var parent = $(this).parent().parent().parent();

                var sub_id = parent.find('#subject_id').val();
                var course_type = parent.find('#course_type').val();
                var level_id = parent.find('#level_id').val();
                var book = parent.find('#book').val();
                var week = parent.find('#week').val();
                // var sub_id = $('#subject_id').val();
                // var level_id = $('#level_id').val();
                // var book = $('#book').val();


                if (course_type == 3) {
                    //parseInt(parent.find('#book_price').val(res.sum));
                } else {
                    $.ajax({
                        type: "GET",
                        dataType: 'JSON',
                        async: false,
                        url: "receipt_school/" + sub_id + "/" + level_id + "/" + book + "/" + week +
                            "/" + course_type,
                        success: function(res) {
                            // console.log(res);
                            parseInt(parent.find('#book_price').val(res.bp));

                            // $('.pp').html(res.html);
                            // console.log($('#book_price').html(res.output));
                            // $("#AddLevel").html(result.html);
                            // $("#AddBook").html(result.html2);
                        }
                    });
                }
            })
            //เทสตรงนี้
            var numen = 0;
            var chackval = 0;
            var entrance_fee = 0;
            var firstRowEntranceFee = 0;
            // var enhide = 0;
            // var enhide = $('#enhide').val();
            $(document).on('change', '.firstTr', function(e) {
                var parent = $(this);
                //var book = parent.find('#book').val();
                var course_type = parent.find('#course_type').val();
                var std_id = parent.find('#std_id').val();
                var sub_id = parent.find('#subject_id').val();
                var level_id = parent.find('#level_id').val();
                var week = parent.find('#week').val();
                var price = parent.find('#price').val();
                var qty = parent.find('#qty').val();
                var discount = parent.find('#discount').val();
                var book_price = parent.find('#book_price').val();
                var type = parent.find('#type').val();
                var media_price = parent.find('#media_price').val();
                var media_name = parent.find('#media_name').val();
                var period = parent.find('#period').val();
                var enhide = $('#enhide').val();

                // console.log('enhide: ' + enhide);

                if ($('#entrance_fee_val').is(':checked')) {
                    entrance_fee = $('#entrance_fee_val').val();
                } else {
                    entrance_fee = 0;
                }

                if (course_type == 2) {
                    parent.find('#qty').prop("disabled", true);
                    parent.find('#week').prop("disabled", true);
                    parent.find('#period').prop("disabled", true);
                    parent.find('#book_price').prop("disabled", true);
                    parent.find('#price').prop("disabled", false);
                    parent.find('#entrance_fee').hide();
                    parent.find('#period').show();
                    parent.find('#media_name').hide();
                    parent.find('#book_price').show();
                    parent.find('#media_price').hide();

                } else if (course_type == 3) {
                    entrance_fee = 0;
                    book_price = null;
                    parent.find('#qty').prop("disabled", true);
                    parent.find('#week').prop("disabled", true);
                    parent.find('#entrance_fee').hide();
                    //parent.find('#price').prop("disabled", true);
                    parent.find('#period').prop("disabled", true);
                    parent.find('#book_price').prop("disabled", true);
                    parent.find('#period').hide();
                    parent.find('#book_price').hide();
                    parent.find('#media_name').show();
                    parent.find('#media_price').show();
                    parent.find('#book_price').val('0');
                } else {
                    parent.find('#period').show();
                    parent.find('#media_name').hide();
                    parent.find('#book_price').show();
                    parent.find('#media_price').hide();
                    parent.find('#entrance_fee').show();
                    parent.find('#qty').prop("disabled", false);
                    parent.find('#week').prop("disabled", false);
                    parent.find('#price').prop("disabled", false);
                    parent.find('#period').prop("disabled", false);
                    parent.find('#book_price').prop("disabled", true);

                }

                if (course_type == 2 || course_type == 3) {
                    week = 0;
                    qty = 0;
                    period = 555;
                    //price = 0;
                }

                if (course_type == 3) {
                    let book = parent.find('#book').val();
                    let ssp = media_price * book;
                    parent.find('.pp').html(
                        '<input type="text" id="pp" name="pp" class="form-control has-feedback-left" value="' +
                        ssp + '" >');
                } else {
                    //alert();
                    if (period != "") {
                        period = 555;
                    }
                    if (enhide > 0) {
                        entrance_fee = 0;
                    }

                    // console.log(period);
                    if (sub_id != "" && level_id != "" && price != "" && discount != "" && book_price !=
                        "" &&
                        type != "" && course_type != "" && period != "") {
                        //alert();
                        $.ajax({
                            type: "GET",
                            dataType: 'JSON',
                            url: "receipt_school/" + sub_id + "/" + level_id + "/" + week + "/" +
                                price + "/" + qty + "/" + discount + "/" + book_price + "/" + type +
                                "/" + course_type + "/" + entrance_fee,
                            success: function(res) {
                                parent.find('.pp').html(res.html);

                            }
                        });
                    }
                }

            });

            // if ($('#entrance_fee_val').is(':checked')) {
            // }
            // Create product Ajax request.
            $('#SubmitCreateForm').click(function(e) {

                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                var arr = new Array();

                $('.firstTr').each(function(i, obj) {
                    if (i > 0) {
                        var entrance_fee = 0;
                        var book_price = 0;
                        if ($(obj).find('#course_type').val() == 3 || $(obj).find('#course_type')
                            .val() == 2) {
                            entrance_fee = 0;

                        } else {
                            if ($(obj).find('#entrance_fee_val').is(':checked')) {
                                entrance_fee = $(obj).find('#entrance_fee_val').val();
                            } else {
                                entrance_fee = 0;
                            }
                        }

                        if ($(obj).find('#course_type').val() == 3) {
                            book_price = 0;
                        } else {
                            book_price = $(obj).find('#book_price').val();
                        }
                        console.log("val ent : " + entrance_fee);
                        var receiptData = {
                            course_type: $(obj).find('#course_type').val(),
                            std_id: $(obj).find('#std_id').val(),
                            subject: $(obj).find('#subject_id').val(),
                            level: $(obj).find('#level_id').val(),
                            week: $(obj).find('#week').val(),
                            qty: $(obj).find('#qty').val(),
                            book: $(obj).find('#book').val(),
                            book_price: book_price,
                            discount: $(obj).find('#discount').val(),
                            price: $(obj).find('#price').val(),
                            period: $(obj).find('#period').val(),
                            pp: $(obj).find('#pp').val(),
                            type: $(obj).find('#type').val(),
                            media_price: $(obj).find('#media_price').val(),
                            media_name: $(obj).find('#media_name').val(),
                            entrance_fee: entrance_fee,
                        }
                        arr.push(receiptData)
                    }
                    //test

                });
                console.log(arr);
                var values = $("input[name='imgFiles2[]']")
                    .map(function() {
                        return $(this).val();
                    }).get();
                if (!$('#AddPromotion').val()) {
                    $('.alert-danger').show();
                    $('.alert-danger').append('<strong><li>กรุณาเลือก Promotion</li></strong>');
                    $('#AddPromotion').focus();
                    return;
                }
                // console.log(values);
                $.ajax({
                    url: "receipt_school/store",
                    method: 'post',
                    data: {
                        /// type: $('#type').val(),
                        img: values,
                        student: $('#AddStudent').val(),
                        submission_date: $('#AddSubmissiondate').val(),
                        payment_type: $('#AddPayment').val(),
                        // first_name: $('#AddFirstname').val(),
                        // last_name: $('#AddLastname').val(),
                        // grade: $('#AddGrade').val(),
                        // description: $('#AddDes').val(),
                        street_address: $('#AddAddress').val(),
                        //street_address2: $('#AddAddress2').val(),
                        //city: $('#AddCity').val(),
                        //province: $('#AddProvince').val(),
                        postcode: $('#AddPostcode').val(),
                        name_parent: $('#AddParent').val()[0],
                        mobile: $('#AddMobile').val(),
                        source: $('#AddSource').val(),
                        referent_parent_id: $('#AddReferentParentId').val(),
                        remark: $('#AddRemark').val(),
                        promotion_id: $('#AddPromotion').val(),
                        calculated_promotion_campaign_ids: GetSelectedCalculatedPromotionIds('create'),
                        is_vat: $('#AddIsVat').is(':checked') ? 1 : 0,
                        receipt: arr,
                        // subject:$('#subject_id').val(),
                        // level:$('#level_id').val(),
                        // week:$('#week').val(),
                        // qty:$('#qty').val(),
                        // book:$('#book').val(),
                        // bookprice:$('#book_price').val(),
                        // discount:$('#discount').val(),
                        // price:$('#price').val(),
                        // total:$('#pp').val(),
                        // type:$('#type').val(),

                        // package: $('#AddPackage').val(),
                        // file_att: $('#AddFile').val(),
                        // sfield: $('#Sfield').val(),
                        _token: token,
                    },
                    success: function(result) {
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            if (result.warnings) {
                                $.each(result.warnings, function(key, value) {
                                    toastr.warning(value, {
                                        timeOut: 8000
                                    });
                                });
                            }
                            $('#CreateModal').modal('hide');
                            $('#Listview').DataTable().ajax.reload();
                            $('.form').trigger('reset');
                            //$('#SubmitCreateForm').hide();
                            //setTimeout(function() {
                            //$('.alert-success').hide();
                            //$('#CreateModal').modal('hide');
                            //}, 10000);

                        }
                    }
                });
            });

            $('#SubmitEditForm').click(function(e) {
                //if (!confirm("Are you sure?")) return;
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                var parent = $(this);
                let rrid = [];

                let student_id = [];

                let rrsub = [];
                let rrcourse = [];
                let rrlevel = [];
                let rrweek = [];
                let rrqty = [];
                let rrbook = [];
                let rrbookprice = [];
                let rrtype = [];
                let rrdiscount = [];
                let rrprice = [];
                let rrtotal = [];
                let rrperiod = [];
                let media_price = [];
                let media_name = [];
                let entrance_fee = [];

                var entrace_edit = $('#entrace_edit').val();
                var entrace_edit_0 = parent.find('#entrace_edit_0').val();



                // Initializing array values
                $("input[name='entrance_fee[]']").each(function() {
                    var feeInput = $(this);
                    if (feeInput.attr('type') === 'hidden') {
                        entrance_fee.push(feeInput.val() || 0);
                        return;
                    }

                    entrance_fee.push(feeInput.is(':checked') ? feeInput.val() : 0);
                });
                $("input[name='rrid[]']").each(function() {
                    rrid.push(this.value);
                });


                $("select[name='student_id[]']").each(function() {
                    student_id.push(this.value);
                });

                $("select[name='rrsub[]']").each(function() {
                    rrsub.push(this.value);
                });

                $("select[name='course_type[]']").each(function() {
                    rrcourse.push(this.value);
                });

                $("select[name='rrlevel[]']").each(function() {
                    rrlevel.push(this.value);
                });

                $("select[name='rrweek[]']").each(function() {
                    rrweek.push(this.value);
                });

                $("select[name='rrqty[]']").each(function() {
                    rrqty.push(this.value);
                });
                $("input[name='rrbook[]']").each(function() {
                    rrbook.push(this.value);
                });
                $("input[name='rrbookprice[]']").each(function() {
                    rrbookprice.push(this.value);
                });
                $("select[name='rrtype[]']").each(function() {
                    rrtype.push(this.value);
                });
                $("input[name='rrdiscount[]']").each(function() {
                    rrdiscount.push(this.value);
                });

                $("select[name='rrprice[]']").each(function() {
                    rrprice.push(this.value);
                });
                $("input[name='rrtotal[]']").each(function() {
                    rrtotal.push(this.value);
                });
                // console.log($("input[name='rrperiod[]']"));
                $("input[name='rrperiod[]']").each(function() {
                    rrperiod.push(this.value);
                });

                $("input[name='media_price[]']").each(function() {
                    media_price.push(this.value);
                });
                $("input[name='media_name[]']").each(function() {
                    media_name.push(this.value);
                });

                // $("input[name='entrance_fee[]']").each(function() {
                //     entrance_fee.push(this.value);
                // });

                var values = $("input[name='imgFiles[]']")
                    .map(function() {
                        return $(this).val();
                    }).get();
                //  console.log(media_price);
                //  console.log(media_name);
                console.log(student_id);
                //รอแก้
                // console.log(id);

                var updatePayload = {
                    img: values,

                    id_parent: $('#id_parent').val(),
                    submission_date: $('#EditSubmissiondate').val(),
                    payment_type: $('#EditPayment').val(),
                    name_parent: $('#EditParent').val()[0],
                    student: $('#EditStudent').val(),
                    description: $('#EditDes').val(),
                    street_address: $('#EditAddress').val(),
                    postcode: $('#EditPostcode').val(),
                    name_parent2: $('#EditParent').val()[0],
                    mobile: $('#EditMobile').val(),
                    source: $('#EditSource').val(),
                    referent_parent_id: $('#EditReferentParentId').val(),
                    remark: $('#EditRemark').val(),
                    promotion_id: $('#EditPromotion').val(),
                    is_vat: $('#EditIsVat').is(':checked') ? 1 : 0,
                    student_id: student_id,
                    rrid: rrid,
                    rrsub: rrsub,
                    rrcourse: rrcourse,
                    rrlevel: rrlevel,
                    rrweek: rrweek,
                    rrqty: rrqty,
                    rrbook: rrbook,
                    rrbookprice: rrbookprice,
                    rrtype: rrtype,
                    rrdiscount: rrdiscount,
                    rrprice: rrprice,
                    rrtotal: rrtotal,
                    rrperiod: rrperiod,
                    media_price: media_price,
                    media_name: media_name,
                    entrance_fee: entrance_fee,
                };

                if ($('#EditCalculatedPromotionTouched').val() === '1') {
                    updatePayload.calculated_promotion_touched = 1;
                    updatePayload.calculated_promotion_campaign_ids = GetSelectedCalculatedPromotionIds('edit');
                }

                $.ajax({
                    url: "receipt_school/save/" + id,
                    method: 'PUT',
                    // async : false,
                    data: updatePayload,

                    success: function(result) {
                        console.log(result);
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            if (result.warnings) {
                                $.each(result.warnings, function(key, value) {
                                    toastr.warning(value, {
                                        timeOut: 8000
                                    });
                                });
                            }
                            $('#EditModal').modal('hide');
                            $('#Listview').DataTable().ajax.reload();
                            //setTimeout(function() {
                            //$('.alert-success').hide();
                            //   $('#EditModal').modal('hide');
                            //}, 10000);

                        }
                    }
                });
            });

            $(document).on('click', '.btn-conv', function(e) {
                e.preventDefault();
                $('.app-container').addClass('closed-sidebar-mobile closed-sidebar');
                $('.alert-danger').html('').hide();
                $('.alert-success').html('').hide();

                let id = $(this).data('id');
                $.ajax({
                    url: "receipt_school/get_conv/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#conv_original_id').val(id);
                        $('#conv_pid').val(res.data.pid);
                        $('#conv_receipt_number').val(res.data.receipt_number);
                        $('#conv_customer_code').val(res.parent ? res.parent.code : '');
                        $('#ConvSubmissiondate').val(new Date().toISOString().slice(0, 10)); // Default to today
                        $('#ConvFirstname').val(res.parent ? res.parent.first_name : '');
                        $('#ConvLastname').val(res.parent ? res.parent.last_name : '');
                        $('#ConvGrade').val(res.data.grade);
                        $('#ConvAddress').val(res.data.street_address);
                        $('#ConvPostcode').val(res.data.postcode);
                        $('#ConvParent').val(res.parent ? res.parent.name_parent : res.data.name_parent);
                        $('#ConvMobile').val(res.data.mobile);
                        
                        $('#myTbl2 tbody').html(res.data_html);
                        $('#ConvModal').modal('show');
                    }
                });
            });

            $('#SubmitConvForm').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                let rrid = [];
                let rrdes = [];
                let rrqty = [];
                let rrunit = [];
                let rrunitp = [];
                let rrdis = [];

                // Initializing array values
                $("input[name='rrid[]']").each(function() {
                    rrid.push(this.value);
                });

                $("input[name='rrdes[]']").each(function() {
                    rrdes.push(this.value);
                });

                $("input[name='rrqty[]']").each(function() {
                    rrqty.push(this.value);
                });

                $("input[name='rrunit[]']").each(function() {
                    rrunit.push(this.value);
                });

                $("input[name='rrunitp[]']").each(function() {
                    rrunitp.push(this.value);
                });

                $("input[name='rrdis[]']").each(function() {
                    rrdis.push(this.value);
                });

                $.ajax({
                    url: "{{ route('receipt_school.conv') }}",
                    method: 'post',
                    data: {
                        type: 2,
                        receipt_number: $('#conv_receipt_number').val(),
                        receipt_customer_number: $('#conv_customer_code').val(),
                        submission_date: $('#ConvSubmissiondate').val(),
                        first_name: $('#ConvFirstname').val(),
                        last_name: $('#ConvLastname').val(),
                        grade: $('#ConvGrade').val(),
                        street_address: $('#ConvAddress').val(),
                        postcode: $('#ConvPostcode').val(),
                        name_parent: $('#ConvParent').val(),
                        mobile: $('#ConvMobile').val(),
                        pid: $('#conv_pid').val(),
                        original_id: $('#conv_original_id').val(),
                        rrdid: rrid,
                        rrdes: rrdes,
                        rrqty: rrqty,
                        rrunit: rrunit,
                        rrunitp: rrunitp,
                        rrdis: rrdis,
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(result) {
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#ConvModal').modal('hide');
                            $('#Listview').DataTable().ajax.reload();
                            $('.form').trigger('reset');
                        }
                    }
                });
            });


            $(document).on('click', '.btn-delete', function() {
                if (!confirm("Are you sure?")) return;

                var rowid = $(this).data('rowid')
                var el = $(this)
                if (!rowid) return;


                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    url: "receipts/destroy/" + rowid,
                    data: {
                        _method: 'delete',
                        _token: token
                    },
                    success: function(data) {
                        if (data.success) {
                            toastr.success(data.message, {
                                timeOut: 5000
                            });
                            table.row(el.parents('tr'))
                                .remove()
                                .draw();
                        }
                    }
                }); //end ajax
            })
        })

        $(function() {
            $.fn.size = () => {
                return this.length;
            }
        });

        $(function() {

            $("#addRow").click(function() {
                // ส่วนของการ clone ข้อมูลด้วย jquery clone() ค่า true คือ
                // การกำหนดให้ ไม่ต้องมีการ ดึงข้อมูลจากค่าเดิมมาใช้งาน
                // รีเซ้ตเป็นค่าว่าง ถ้ามีข้อมูลอยู่แล้ว ทั้ง select หรือ input

                var entrance_fee = 0;
                var flown = parseInt($('#flown').val()) + 1;
                $('#flown').val(flown)
                var newrow = $("#tr1").clone(true);
                newrow.show();
                $("#myTbl").append(newrow);
                $('.more_reservation1').daterangepicker({
                    locale: {
                        format: 'YYYY-MM'
                    }
                });
                window.ScheduleCalculatedPromotionPreview('create', false);
            });
            $("#removeRow").click(function() {
                // // ส่วนสำหรับการลบ
                if ($("#myTbl tr").length > 3) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                    $("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                    window.ScheduleCalculatedPromotionPreview('create', false);
                } else {
                    // เหลือ 1 รายการลบไม่ได้
                    if ($("#myTbl tr").length > 1) {
                        //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                        toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                            timeOut: 5000
                        });
                    }
                }
            });
            $(".btnRemoveg").click(function() {
                // // ส่วนสำหรับการลบ
                if ($("#myTbl tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                    //$("#myTbl tr:last").remove(); // ลบรายการสุดท้าย

                    $(this).closest("tr").remove();
                    window.ScheduleCalculatedPromotionPreview('create', false);
                } else {
                    // เหลือ 1 รายการลบไม่ได้
                    if ($("#myTbl tr").length > 1) {
                        //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                        toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                            timeOut: 5000
                        });
                    }
                }
            });

            $("#addRow2").click(function() {
                // ส่วนของการ clone ข้อมูลด้วย jquery clone() ค่า true คือ
                // การกำหนดให้ ไม่ต้องมีการ ดึงข้อมูลจากค่าเดิมมาใช้งาน
                // รีเซ้ตเป็นค่าว่าง ถ้ามีข้อมูลอยู่แล้ว ทั้ง select หรือ input
                $(".firstTr3:eq(0)").clone(true)


                    .find("input").attr("value", "").end()
                    .find("select").attr("value", "").end()
                    .appendTo($("#myTbl3"));
                window.ScheduleCalculatedPromotionPreview('edit', true);
            });
            $("#removeRow2").click(function() {
                // // ส่วนสำหรับการลบ
                if ($("#myTbl3 tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                    $("#myTbl3 tr:last").remove(); // ลบรายการสุดท้าย
                    window.ScheduleCalculatedPromotionPreview('edit', true);
                } else {
                    // เหลือ 1 รายการลบไม่ได้
                    if ($("#myTbl3 tr").length > 1) {
                        //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                        toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                            timeOut: 5000
                        });
                    }
                }
            });
            $(".btnRemoveg2").click(function() {
                // // ส่วนสำหรับการลบ
                if ($("#myTbl3 tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                    //$("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                    $(this).closest("tr").remove();
                    window.ScheduleCalculatedPromotionPreview('edit', true);
                } else {
                    // เหลือ 1 รายการลบไม่ได้
                    if ($("#myTbl3 tr").length > 1) {
                        //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                        toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                            timeOut: 5000
                        });
                    }
                }
            });

            $(this).closest(".abcd").remove();

        });

        var myDropzone = {};


        Dropzone.options.myAwesomeDropzone2 = {
            url: '{{ route('receipt_school.upload') }}',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
            autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
            uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
            parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
            maxFiles: 5, // ไฟล์สูงสุด 5 ไฟล์
            maxfilesexceeded: function(file) {
                this.removeAllFiles();
                this.addFile(file);
            },
            addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
            maxFilesize: 10, // MB
            renameFile: function(file) {
                let newName = new Date().getTime() + '_' + file.name;
                file.newName = newName;
                return newName;
            },
            previewsContainer: "#dropzone_preview2", // ระบุ element เป้าหลาย
            //previewTemplate: $('#template-preview').html(),
            dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
            dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
            dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Browse File</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
            dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
            acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้
            // The setting up of the dropzone
            init: function() {
                var myDropzone = this;
                // var mockFile = { name: 'public/images/infinite.jpg', size: 12345, type: 'image/jpeg' };
                // myDropzone.emit("addedfile", mockFile);
                // myDropzone.emit("success", mockFile);
                // myDropzone.emit("thumbnail", mockFile, "https://example.com")
                // myDropzone = this;
                this.on("addedfile", function(file) {
                    //$('#infinite').append("<input type='text' class='form_none' name='imgFiles[]' value='" + file.name + "'/>");
                    $('#infinite').append("<input type='text' id='" + file.newName +
                        "' class='form_none' name='imgFiles2[]' value='" + file.newName + "'/>");
                    file.previewElement.id = file.newName;
                }).on("removedfile", function(file) {
                    //alert(file.id);
                    //var name = file.name;
                    var name = file.previewElement.id;

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        type: 'POST',
                        url: '{{ route('receipt_school.delete') }}',
                        data: {
                            name: name,
                        },
                        success: function(data) {
                            console.log('success: ' + data);
                            var element = document.getElementById("infinite");
                            var child = document.getElementById(name);
                            element.removeChild(child);
                            if (!document.getElementsByName('imgFiles2[]').length) {
                                //alert();
                                $('#dropzone_preview2').css("display", "none");
                                $('#dropzone-att').addClass('form-focus-error');
                                $('#dropzone-att')
                                    .find('.form-error-message').css("display", "block");
                                $('#dropzone-att').css('border', 'solid 1px red');
                            }
                        }
                    });

                    //var _ref;
                    //return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;

                }).on("maxfilesexceeded", function(file) {
                    alert('allow maximum 5 file');
                    this.removeFile(file);
                }).on('complete', function(file) {
                    let val = file.accepted;
                    if (file.accepted == true) {
                        obj = JSON.parse(file.xhr.response);
                        //file.previewElement.id = obj.success;
                        // console.log(obj.success);
                        //alert(obj.success);
                        //$('#infinite').append("<input type='text' id='" + obj.success +
                        //   "' class='form_none' name='imgFiles2[]' value='" + obj.success + "'/>");
                    }
                    let val1 = file.name;
                    $('#dropzone_preview2').css("display", "block");
                    $('#dropzone-att').removeClass('form-focus-error');
                    $('#dropzone-att')
                        .find('.form-error-message').css("display", "none");
                    $('#dropzone-att').css('border', 'solid 1px green');
                    if (document.getElementsByName('imgFiles2[]').length) {
                        //$('#error_div').css('display', 'none');
                    }
                }).on("success", function(file) {
                    //var responseText = file.id // or however you would point to your assigned file ID here;
                    //console.log(response); // console should show the ID you pointed to
                    // do stuff with file.id ...
                });
                /*this.on("addedfile", function(file) {

                }).on("removedfile", function(file) {

                }).on("thumbnail", function(file) {

                }).on("error", function(file) {

                }).on("processing", function(file) {

                }).on("uploadprogress", function(file) {

                });*/

            }
        }

        Dropzone.options.myAwesomeDropzone = {
            url: '{{ route('receipt_school.upload') }}',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
            autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
            uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
            parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
            maxFiles: 5, // ไฟล์สูงสุด 5 ไฟล์
            maxfilesexceeded: function(file) {
                this.removeAllFiles();
                this.addFile(file);
            },
            addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
            maxFilesize: 10, // MB
            previewsContainer: "#dropzone_preview", // ระบุ element เป้าหลาย
            //previewTemplate: $('#template-preview').html(),
            dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
            renameFile: function(file) {
                let newName = new Date().getTime() + '_' + file.name;
                file.newName = newName;
                return newName;
            },
            dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
            dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Browse File</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
            dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
            acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้

            // The setting up of the dropzone
            init: function() {
                myDropzone = this;
                this.on("addedfile", function(file) {
                    //$('#infinite').append("<input type='text' class='form_none' name='imgFiles[]' value='" + file.newName + "'/>");
                    $('#infiniteEdit').append("<input type='text' id='" + file.newName +
                        "' class='form_none' name='imgFiles[]' value='" + file.newName + "'/>");
                    file.previewElement.id = file.newName;
                }).on("removedfile", function(file) {
                    //alert(file.id);
                    //var name = file.name;
                    var name = file.previewElement.id;

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        type: 'POST',
                        url: '{{ route('receipt_school.delete') }}',
                        data: {
                            name: name,
                        },
                        success: function(data) {
                            console.log('success: ' + data);
                            var element = document.getElementById("infinite");
                            var child = document.getElementById(name);
                            element.removeChild(child);
                            if (!document.getElementsByName('imgFiles[]').length) {
                                $('#dropzone_preview').css("display", "none");
                                $('#dropzone-photo').addClass('form-focus-error');
                                $('#dropzone-photo')
                                    .find('.form-error-message').css("display", "block");
                                $('#dropzone-photo').css('border', 'solid 1px red');
                            }
                        }
                    });

                    //var _ref;
                    //return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;

                }).on("maxfilesexceeded", function(file, response) {
                    alert('allow maximum 5 file');
                    this.removeFile(file);
                }).on('complete', function(file) {
                    let val = file.accepted;
                    if (file.accepted == true) {
                        obj = JSON.parse(file.xhr.response);
                        //file.previewElement.id = obj.success;
                        console.log(obj.success);
                        //$('#infinite').append("<input type='text' id='" + obj.success +
                        //    "' class='form_none' name='imgFiles[]' value='" + obj.success + "'/>");
                    }
                    let val1 = file.name;
                    $('#dropzone_preview').css("display", "block");
                    $('#dropzone-photo').removeClass('form-focus-error');
                    $('#dropzone-photo')
                        .find('.form-error-message').css("display", "none");
                    $('#dropzone-photo').css('border', 'solid 1px green');
                    if (document.getElementsByName('imgFiles[]').length) {
                        //$('#error_div').css('display', 'none');
                    }
                }).on("success", function(file) {
                    //var responseText = file.id // or however you would point to your assigned file ID here;
                    //console.log(response); // console should show the ID you pointed to
                    // do stuff with file.id ...
                });
                /*this.on("addedfile", function(file) {

                }).on("removedfile", function(file) {

                }).on("thumbnail", function(file) {

                }).on("error", function(file) {

                }).on("processing", function(file) {

                }).on("uploadprogress", function(file) {

                });*/

            }
        }
    </script>
@endsection
