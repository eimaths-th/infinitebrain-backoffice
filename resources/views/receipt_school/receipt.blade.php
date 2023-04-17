@extends('layouts.main_template')

@section('style')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://malsup.github.io/jquery.blockUI.js"></script>
<script src="https://rawgit.com/RobinHerbots/Inputmask/5.x/dist/jquery.inputmask.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@endsection

@section('content')
<div class="row">

    @if ($message = Session::get('success'))
    <!--<div class="alert alert-success">
                                <p>{{ $message }}</p>
                                    </div>-->
    <script>
        toastr.success('{{ $message }}', {
            timeOut: 5000
        });
    </script>
    @endif

    @if ($message = Session::get('error'))
    <!--<div class="alert alert-success">
                                        <p>{{ $message }}</p>
                                            </div>-->
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
                    <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg" id="CreateButton"><i class="fa fa-plus"></i> Create New
                        Receipt</a>
                    <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                        All</a>

                </div>
                <div class="d-inline-block dropdown">
                    <div class="dropdown show">
                        <button class="btn btn-shadow btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" autoClose="true" aria-haspopup="true" aria-expanded="false">
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

                </div>



            </div>
        </div>
    </div>
    <div class="main-card mb-3 card " id="x_content">
        <div class="card-body">
            <form method="post" action="{{ route('receipts.destroy_all') }}" name="delete_all" id="delete_all">
                @csrf
                @method('DELETE')
                <table style="width: 100%;" id="Listview" class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            <th width="10px"><input type="checkbox" id="check-all" class="flat"></th>
                            <th>Type</th>
                            <th>Submission Date</th>
                            <th>Voucher Number</th>
                            <th>Parent Name</th>

                            <th>Mobile</th>
                            <th>Address</th>
                            <th>Postcode</th>
                            <th width="150px">Student</th>
                            <th width="280px">Action</th>
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
<div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="PrintDayModal">
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
                            <input type="text" class="AddDate form-control" name="print_date" id="PrintDaySubmissiondate" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="Submission Date">รหัสเอกสาร:</label>
                            <input type="number" class="form-control" name="print_number" id="PrintDayNumber" value="" required>
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
<div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="PrintMonthModal">
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
                            <!-- <input type="hidden" name="pmsfield" value="{{ request()->get('sfield') }}"> -->
                        </div>
                        <div class="form-group">
                            <label for="Submission Month">Print Month:</label>
                            <input type="text" class="AddMonth form-control" name="print_month" id="PrintMonthSubmissiondate" value="" required>
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
<div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="ImportModal">
    <div class="modal-dialog modal-lg" role="document">
        <form id="importForm" class="form" action="{{ route('receipts.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Import Receipt</h5>
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

<div class="fade modal bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="CreateModal">
    <div class="modal-dialog modal-xl" role="document">
        <form class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add </h5>
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

                    <div id="CreateModalBody">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a data-toggle="tab" href="#tab-eg10-0" class="active nav-link">ข้อมูลลูกค้า</a></li>
                            <li class="nav-item"><a data-toggle="tab" href="#tab-eg10-1" class="nav-link">รายการในใบเสร็จ</a></li>

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
                                            <input type="text" class="AddDate form-control" name="submission_date" id="AddSubmissiondate" value="">
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6">
    <div class="position-relative  form-group">
        <label for="Package">Package:</label>
        <input type="text" class="form-control" name="package" id="AddPackage"
            value="">
    </div>
</div> -->
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Parent">Name Parent:</label>
                                            <input type="text" class="form-control" name="name_parent" id="AddParent" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Mobile">Mobile:</label>
                                            <input type="text" class="AddPhone form-control" name="mobile" id="AddMobile" value="">
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
                                    <textarea class="form-control" name="address" id="AddAddress"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="Postcode">Postcode:</label>
                                    <input type="text" class="form-control" name="postcode" id="AddPostcode" value="">
                                </div>



                                <div class="form-group">
                                    <label for="Student">Student:</label><br>
                                    <select style="width: 100%;" class="select2 select2_single form-control" id="AddStudent" name="student[]" multiple="multiple">
                                        <!-- <option value="" selected>Select Student</option> -->
                                        @foreach ($data2 as $key2)
                                        <option value="{{ $key2->id }}">{{ $key2->code }} {{ $key2->first_name }} {{ $key2->last_name }}
                                        </option>
                                        @endforeach

                                    </select>
                                </div>





                            </div>
                            <div class="tab-pane" id="tab-eg10-1" role="tabpanel">

                                <table id="myTbl" class="table table-striped table-bordered responsive-utilities jambo_table " width="90%" align="center">
                                    <thead>

                                        <tr class="headings">

                                            <th class="column-title"> NO.</th>
                                            <th class="column-title"> Subject</th>
                                            <th class="column-title abc">Type</th>
                                            <th class="column-title"> Start Level</th>
                                            <th class="column-title"> Week</th>
                                            <th class="column-title"> Qty</th>
                                            <th class="column-title"> Book</th>
                                            <th class="column-title"> Book Price</th>
                                            <th class="column-title"> Discount</th>
                                            <th class="column-title"> Price</th>
                                            <th class="column-title"> Total</th>
                                            {{-- <th class="column-title"> </th> --}}
                                        </tr>

                                    </thead>

                                    <tbody>
                                        <tr class="firstTr" style="display:none" id="tr1">
                                            <td width="5%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="1" required="required">

                                                </div>
                                            </td>
                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class="aaa form-control" id="subject_id" name="subject_id" required>
                                                        <option value="" selected>Select Subject</option>

                                                        @foreach ($data3 as $key2)
                                                        <option value="{{ $key2->id }}">{{ $key2->title }}
                                                        </option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </td>

                                            <td width="10%" class="aabbcc">
                                                <div class="col-md-12 col-sm-12 col-xs-12 pm">
                                                    <select style="width: 100%;" class=" form-control" id="type" name="type">
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

                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class=" form-control" id="level_id" name="level_id">



                                                    </select>
                                                </div>
                                            </td>

                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class=" form-control" id="week" name="week" required>
                                                        <option value="" selected></option>

                                                        <option value="1">1 week
                                                        </option>
                                                        <option value="2">2 week
                                                        </option>
                                                        <option value="3">3 week
                                                        </option>
                                                        <option value="4">4 week
                                                        </option>

                                                    </select>
                                                </div>
                                            </td>
                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class=" form-control" id="qty" name="qty" required>
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

                                                    </select>
                                                </div>
                                            </td>
                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                    <input type="text" class="form-control" name="book" id="book" value="">

                                                </div>
                                            </td>
                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 cc">

                                                </div>
                                            </td>

                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                    <input type="text" class="form-control" name="discount" id="discount" value="">

                                                </div>
                                            </td>
                                            <td width="10%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class="bbb form-control" id="price" name="price" required>
                                                        <option value="" selected>Select Price</option>

                                                        <option value="1">Old Price
                                                        </option>
                                                        <option value="2">New Price
                                                        </option>

                                                    </select>
                                                </div>
                                            </td>


                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 pp">
                                                </div>
                                            </td>


                                            {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                        </tr>
                                        <tr class="firstTr">
                                            <td width="5%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="1" required="required">

                                                </div>
                                            </td>
                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class="aaa form-control" id="subject_id" name="subject_id" required>
                                                        <option value="" selected>Select Subject</option>

                                                        @foreach ($data3 as $key2)
                                                        <option value="{{ $key2->id }}">{{ $key2->title }}
                                                        </option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </td>

                                            <td width="10%" class="aabbcc">
                                                <div class="col-md-12 col-sm-12 col-xs-12 pm">
                                                    <select style="width: 100%;" class=" form-control" id="type" name="type">
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

                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class=" form-control" id="level_id" name="level_id">



                                                    </select>
                                                </div>
                                            </td>

                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class=" form-control" id="week" name="week" required>
                                                        <option value="" selected></option>

                                                        <option value="1">1 week
                                                        </option>
                                                        <option value="2">2 week
                                                        </option>
                                                        <option value="3">3 week
                                                        </option>
                                                        <option value="4">4 week
                                                        </option>

                                                    </select>
                                                </div>
                                            </td>
                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class=" form-control" id="qty" name="qty" required>
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

                                                    </select>
                                                </div>
                                            </td>
                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                    <input type="text" class="form-control" name="book" id="book" value="">

                                                </div>
                                            </td>
                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 cc">

                                                </div>
                                            </td>

                                            <td width="7%">
                                                <div class="col-md-12 col-sm-12 col-xs-12 ">
                                                    <input type="text" class="form-control" name="discount" id="discount" value="">

                                                </div>
                                            </td>
                                            <td width="10%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <select style="width: 100%;" class="bbb form-control" id="price" name="price" required>
                                                        <option value="" selected>Select Price</option>

                                                        <option value="1">Old Price
                                                        </option>
                                                        <option value="2">New Price
                                                        </option>

                                                    </select>
                                                </div>
                                            </td>


                                            <td width="15%">
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

                                    <button type="button" id="addRow" class="btn btn-sm btn-primary btnAddg"><i class="fa fa-plus"></i></button>
                                    <button type="button" id="removeRow" class="btn btn-sm btn-danger btnRemoveg"><i class="fa fa-minus"></i></button>
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
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
    <div class="modal-dialog modal-lg" role="document">
        <form class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Edit </h5>
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
                        <strong>Success!</strong> Receipt was edit successfully.
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>

                    <div id="EditModalBody">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a data-toggle="tab" href="#tab-eg10-0" class="active nav-link">ข้อมูลลูกค้า</a></li>
                            <li class="nav-item"><a data-toggle="tab" href="#tab-eg10-1" class="nav-link">รายการในใบเสร็จ</a></li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-eg10-0" role="tabpanel">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="ReceiptNumber">Voucher Number:</label>
                                            <input type="text" class="form-control" name="receipt_number" id="receipt_number" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative  form-group">
                                            <label for="ReceiptCNumber">Customer Code:</label>
                                            <input type="text" class="form-control" name="customer_code" id="customer_code" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="Submission Date">Submission Date:</label>
                                            <input type="text" class="AddDate form-control" name="submission_date" id="EditSubmissiondate" value="">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="Grade">Grade:</label>
                                            <input type="text" class="form-control" name="grade" id="EditGrade" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="firstname">First Name:</label>
                                            <input type="text" class="form-control" name="firstname" id="EditFirstname" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="lastname">Last Name:</label>
                                            <input type="text" class="form-control" name="lastname" id="EditLastname" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Parent">Name Parent:</label>
                                            <input type="text" class="form-control" name="name_parent" id="EditParent" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Mobile">Mobile:</label>
                                            <input type="text" class="AddPhone form-control" name="mobile" id="EditMobile" value="">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Postcode">Postcode:</label>
                                            <input type="text" class="form-control" name="postcode" id="EditPostcode" value="">
                                        </div>
                                    </div>
                                </div>

                                <!--<div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="City">City:</label>
                                            <input type="text" class="form-control" name="city" id="EditCity"
                                                value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="Province">Province:</label>
                                            <input type="text" class="form-control" name="province" id="EditProvince"
                                                value="">
                                        </div>
                                    </div>
                                    </div>-->


                                <div class="form-group">
                                    <label for="Name">Street Address:</label>
                                    <textarea class="form-control" name="address" id="EditAddress"></textarea>
                                </div>
                                <!--<div class="form-group">
                                        <label for="Name">Street Address 2:</label>
                                        <textarea class="form-control" name="address2" id="EditAddress2"></textarea>
                                    </div>-->



                            </div>
                            <div class="tab-pane" id="tab-eg10-1" role="tabpanel">

                                <table id="myTbl" class="table table-striped table-bordered responsive-utilities jambo_table " width="90%" align="center">
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
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="1" required="required">

                                                </div>
                                            </td>
                                            <td width="35%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>
                                            <td width="10%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>

                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>
                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>

                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>

                                            {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                        </tr>
                                    </tbody>


                                </table>
                                <div class="col-md-12" align="right">

                                    <button type="button" id="addRow" class="btn btn-sm btn-primary btnAddg"><i class="fa fa-plus"></i></button>
                                    <button type="button" id="removeRow" class="btn btn-sm btn-danger btnRemoveg"><i class="fa fa-minus"></i></button>
                                </div>

                            </div>
                        </div>
                        <!--tab-->
                    </div>

                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="SubmitEditForm">Update</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- Convert credit note  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="ConvModal">
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
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                        <strong>Success!</strong> Credit Note was create successfully.
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>

                    <div id="ConvModalBody">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a data-toggle="tab" href="#tab-eg20-0" class="active nav-link">ข้อมูลลูกค้า</a></li>
                            <li class="nav-item"><a data-toggle="tab" href="#tab-eg20-1" class="nav-link">รายการในใบเสร็จ</a></li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-eg20-0" role="tabpanel">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="ReceiptNumber">Voucher Number:</label>
                                            <input type="text" class="form-control" name="conv_receipt_number" id="conv_receipt_number" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative  form-group">
                                            <label for="ReceiptCNumber">Customer Code:</label>
                                            <input type="text" class="form-control" name="conv_customer_code" id="conv_customer_code" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="Submission Date">Submission Date:</label>
                                            <input type="text" class="AddDate form-control" name="conv_submission_date" id="ConvSubmissiondate" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="Grade">Grade:</label>
                                            <input type="text" class="form-control" name="conv_grade" id="ConvGrade" value="">
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
                                            <input type="text" class="form-control" name="conv_firstname" id="ConvFirstname" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="lastname">Last Name:</label>
                                            <input type="text" class="form-control" name="conv_lastname" id="ConvLastname" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Parent">Name Parent:</label>
                                            <input type="text" class="form-control" name="conv_name_parent" id="ConvParent" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Mobile">Mobile:</label>
                                            <input type="text" class="AddPhone form-control" name="conv_mobile" id="ConvMobile" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Postcode">Postcode:</label>
                                            <input type="text" class="form-control" name="conv_postcode" id="ConvPostcode" value="">
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
                            <div class="tab-pane" id="tab-eg20-1" role="tabpanel">

                                <table id="myTbl2" class="table table-striped table-bordered responsive-utilities jambo_table " width="90%" align="center">
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
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="1" required="required">

                                                </div>
                                            </td>
                                            <td width="35%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>
                                            <td width="10%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>

                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>
                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>

                                            <td width="15%">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="flown[]" name="flown[]" class="form-control has-feedback-left" value="" required="required">
                                                </div>
                                            </td>

                                            {{-- <td><button type="button" id="removeRowt"
                                                        class="btn btn-sm btn-danger btnRemoveg"><i
                                                            class="fa fa-minus"></i></button></td> --}}

                                        </tr>
                                    </tbody>


                                </table>
                                <div class="col-md-12" align="right">

                                    <button type="button" id="addRow2" class="btn btn-sm btn-primary btnAddg2"><i class="fa fa-plus"></i></button>
                                    <button type="button" id="removeRow2" class="btn btn-sm btn-danger btnRemoveg2"><i class="fa fa-minus"></i></button>
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
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // $('.abc').prop( "disabled", true );
        $('#type').prop("disabled", true);
        // $("input").attr('disabled','disabled');
        $(".nav-tabs a").click(function() {
            $(this).tab('show');
        });

        $(".select2_single").select2({
            maximumSelectionLength: 2,
            allowClear: true
        });
        $(".select2_multiple").select2({
            maximumSelectionLength: 2,
            //placeholder: "With Max Selection limit 4",
            allowClear: true
        });
        $('.dropdown-toggle').dropdown();

        $(".AddDate").datepicker({
            dateFormat: "yy-mm-dd"
        });

        $(".AddMonth").datepicker({
            dateFormat: "yy-mm"
        });

        $(".AddDate").inputmask("9999-99-99");
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

        var table = $('#Listview').DataTable({
            /*"aoColumnDefs": [
            {
            'bSortable': true,
            'aTargets': [0]
            } //disables sorting for column one
            ],
            "searching": false,
            "lengthChange": false,
            "paging": false,
            'iDisplayLength': 10,
            "sPaginationType": "full_numbers",
            "dom": 'T<"clear">lfrtip',
                */
            ajax: '',
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
                    name: 'action'
                },
            ]
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



        $(document).on('click', '#ImportButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('#ImportModal').modal('show');
        });

        let id;
        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            id = $(this).data('id');
            $.ajax({
                url: "receipts/edit/" + id + "/0",
                method: 'GET',
                success: function(res) {
                    //console.log(res);
                    //$('#EditModalBody').html(res.html);
                    //console.log(res.data_html);
                    $('#receipt_number').val(res.data.receipt_number);
                    $('#customer_code').val(res.data.receipt_customer_number);
                    $('#EditSubmissiondate').val(res.data.submission_date);
                    //$('#EditPackage').val(res.data.package);
                    $('#EditFirstname').val(res.data.first_name);
                    $('#EditLastname').val(res.data.last_name);
                    $('#EditDes').val(res.data.description);
                    $('#EditGrade').val(res.data.grade);
                    $('#EditAddress').val(res.data.street_address);
                    //$('#EditAddress2').val(res.data.street_address2);
                    //$('#EditCity').val(res.data.city);
                    //$('#EditProvince').val(res.data.province);
                    $('#EditPostcode').val(res.data.postcode);
                    $('#EditParent').val(res.data.name_parent);
                    $('#EditMobile').val(res.data.mobile);
                    $('#myTbl tbody').html(res.data_html);
                    //append
                    //$('#EditFile').val(res.data.file_att);
                    $('#EditModal').modal('show');

                }
            });

        });


        $(document).on('click', '#getConvData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            id = $(this).data('id');
            $.ajax({
                url: "receipts/edit/" + id + "/2",
                method: 'GET',
                success: function(res) {
                    //console.log(res.data_html);
                    //$('#conv_receipt_number').val(res.data.receipt_number);
                    $('#conv_customer_code').val(res.data.receipt_customer_number);
                    //$('#ConvSubmissiondate').val(res.data.submission_date);
                    $('#ConvPackage').val(res.data.package);
                    $('#ConvFirstname').val(res.data.first_name);
                    $('#ConvLastname').val(res.data.last_name);
                    $('#ConvDes').val(res.data.description);
                    $('#ConvGrade').val(res.data.grade);
                    $('#ConvAddress').val(res.data.street_address);
                    //$('#EditAddress2').val(res.data.street_address2);
                    //$('#EditCity').val(res.data.city);
                    //$('#EditProvince').val(res.data.province);
                    $('#ConvPostcode').val(res.data.postcode);
                    $('#ConvParent').val(res.data.name_parent);
                    $('#ConvMobile').val(res.data.mobile);
                    $('#ConvFile').val(res.data.file_att);
                    $('#myTbl2 tbody').html(res.data_html);
                    $('#ConvModal').modal('show');

                }
            });

        })
        /////////
        let sub_id;

        $(".aaa").change(function() {
            // console.log($(this).parent().parent().parent().html())
            // console.log($(this).parent().parent().parent().find('#type').html())
            // $(this).parent().parent().parent().find('#discount').val(new Date().toDateString())
            // $('#discount').val(new Date().toDateString())
            var parent = $(this).parent().parent().parent();
            sub_id = $(this).parent().parent().parent().find('#subject_id').val();
            if (sub_id == 3) {
                // parent.find('.abc').prop( "disabled", false );
                parent.find('#type').prop("disabled", false);

            } else {
                // parent.find('.abc').prop( "disabled", true );
                parent.find('#type').prop("disabled", true);
            }


            $.ajax({
                method: "GET",
                url: "receipt_school/" + sub_id,
                success: function(res) {
                    parent.find('#level_id').html(res.html);
                    // console.log(res);

                }
            });
        })

        ////////


        // $(document).on('change', '.aaa', function(e) {
        //     e.preventDefault();
        //     sub_id = $('#subject_id').val();

        //     if(sub_id==3){
        //         $('.abc').show();
        //         $('.aabbcc').show();

        //     }else{
        //         $('.abc').hide();
        //         $('.aabbcc').hide();
        //     }
        //     // console.log(sub_id);
        //     $.ajax({
        //         method: "GET",
        //         url: "receipt_school/" + sub_id ,
        //         success: function(res) {
        //                 $('#level_id').html(res.html);
        //                 // console.log(res);

        //         }
        //     }); //end ajax
        // })


        $(document).on('change', '#book', function(e) {
            var parent = $(this).parent().parent().parent();

            var sub_id = parent.find('#subject_id').val();
            var level_id = parent.find('#level_id').val();
            var book = parent.find('#book').val();

            // var sub_id = $('#subject_id').val();
            // var level_id = $('#level_id').val();
            // var book = $('#book').val();


            $.ajax({
                type: "GET",
                dataType: 'JSON',
                url: "receipt_school/" + sub_id + "/" + level_id + "/" + book,
                success: function(res) {
                    parent.find('.cc').html(res.html);

                    // $('.pp').html(res.html);
                    //console.log(result);
                    // $("#AddLevel").html(result.html);
                    // $("#AddBook").html(result.html2);
                }
            });

        })
        $(document).on('change', '.firstTr', function(e) {
            var parent = $(this);

            var sub_id = parent.find('#subject_id').val();
            var level_id = parent.find('#level_id').val();
            var week = parent.find('#week').val();
            var price = parent.find('#price').val();
            var qty = parent.find('#qty').val();
            var discount = parent.find('#discount').val();
            var book_price = parent.find('#book_price').val();
            var type = parent.find('#type').val();
            // console.log(subject_id);
            // console.log(level_id);
            // console.log(week);
            // console.log(price);
            // console.log(qty);
            // console.log(discount);
            // console.log(book_price);
            // console.log(type);

            if (sub_id != "" && level_id != "" && week != "" && price != "" && price != "" && qty != "" && discount != "" && book_price != "" && type != "") {
                $.ajax({
                    type: "GET",
                    dataType: 'JSON',
                    url: "receipt_school/" + sub_id + "/" + level_id + "/" + week + "/" + price + "/" + qty + "/" + discount + "/" + book_price + "/" + type,
                    success: function(res) {
                        parent.find('.pp').html(res.html);
                        //console.log(result);
                        // $("#AddLevel").html(result.html);
                        // $("#AddBook").html(result.html2);
                    }
                });
            }

        })
        // $(document).on('change', '#type', function(e) {
        // sum()


        // });
        // $(document).on('change', '#discount', function(e) {
        // sum()


        // });
        // $(document).on('change', '#book', function(e) {
        // sum()


        // });$(document).on('change', '#qty', function(e) {
        // sum()


        // });$(document).on('change', '#level_id', function(e) {
        // sum()


        // });
        // $(document).on('change', '#subject_id', function(e) {
        // sum()


        // });
        // $(document).on('change', '#week', function(e) {
        // sum()


        // });
        // $(document).on('change', '.bbb', function(e) {
        // sum()


        // });

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
                    // console.log($(obj).find('#price').val());
                    var receiptData = {
                        subject: $(obj).find('#subject_id').val(),
                        level: $(obj).find('#level_id').val(),
                        week: $(obj).find('#week').val(),
                        qty: $(obj).find('#qty').val(),
                        book: $(obj).find('#book').val(),
                        book_price: $(obj).find('#book_price').val(),
                        discount: $(obj).find('#discount').val(),
                        price: $(obj).find('#price').val(),
                        pp: $(obj).find('#pp').val(),
                        type: $(obj).find('#type').val(),
                    }
                    arr.push(receiptData)
                }
                //test
            });
            $.ajax({
                url: "{{ route('receipt_school.store') }}",
                method: 'post',
                data: {
                    // type: $('#type').val(),
                    student: $('#AddStudent').val(),
                    submission_date: $('#AddSubmissiondate').val(),
                    // first_name: $('#AddFirstname').val(),
                    // last_name: $('#AddLastname').val(),
                    // grade: $('#AddGrade').val(),
                    // description: $('#AddDes').val(),
                    street_address: $('#AddAddress').val(),
                    //street_address2: $('#AddAddress2').val(),
                    //city: $('#AddCity').val(),
                    //province: $('#AddProvince').val(),
                    postcode: $('#AddPostcode').val(),
                    name_parent: $('#AddParent').val(),
                    mobile: $('#AddMobile').val(),
                    receipt: arr ,
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

            let rid = [];
            let rdes = [];
            let rqty = [];
            let runit = [];
            let runitp = [];
            let rdis = [];

            // Initializing array values
            $("input[name='rid[]']").each(function() {
                rid.push(this.value);
            });


            $("input[name='rdes[]']").each(function() {
                rdes.push(this.value);
            });

            $("input[name='rqty[]']").each(function() {
                rqty.push(this.value);
            });

            $("input[name='runit[]']").each(function() {
                runit.push(this.value);
            });

            $("input[name='runitp[]']").each(function() {
                runitp.push(this.value);
            });

            $("input[name='rdis[]']").each(function() {
                rdis.push(this.value);
            });


            $.ajax({
                url: "receipts/save/" + id,
                method: 'PUT',
                data: {
                    submission_date: $('#EditSubmissiondate').val(),
                    first_name: $('#EditFirstname').val(),
                    last_name: $('#EditLastname').val(),
                    grade: $('#EditGrade').val(),
                    description: $('#EditDes').val(),
                    street_address: $('#EditAddress').val(),
                    //street_address2: $('#EditAddress2').val(),
                    //city: $('#EditCity').val(),
                    //province: $('#EditProvince').val(),
                    postcode: $('#EditPostcode').val(),
                    name_parent: $('#EditParent').val(),
                    mobile: $('#EditMobile').val(),
                    package: $('#EditPackage').val(),
                    file_att: $('#EditFile').val(),
                    rdid: rid,
                    rdes: rdes,
                    rqty: rqty,
                    runit: runit,
                    runitp: runitp,
                    rdis: rdis,
                },

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
                url: "{{ route('receipts.conv') }}",
                method: 'post',
                data: {
                    type: 2,
                    receipt_number: $('#conv_receipt_number').val(),
                    receipt_customer_number: $('#conv_customer_code').val(),
                    submission_date: $('#ConvSubmissiondate').val(),
                    first_name: $('#ConvFirstname').val(),
                    last_name: $('#ConvLastname').val(),
                    grade: $('#ConvGrade').val(),
                    //description: $('#ConvDes').val(),
                    street_address: $('#ConvAddress').val(),
                    //street_address2: $('#AddAddress2').val(),
                    //city: $('#AddCity').val(),
                    //province: $('#AddProvince').val(),
                    postcode: $('#ConvPostcode').val(),
                    name_parent: $('#ConvParent').val(),
                    mobile: $('#ConvMobile').val(),
                    //package: $('#ConvPackage').val(),
                    //file_att: $('#AddFile').val(),
                    // sfield: $('#Sfield').val(),
                    rrdid: rrid,
                    rrdes: rrdes,
                    rrqty: rrqty,
                    rrunit: rrunit,
                    rrunitp: rrunitp,
                    rrdis: rrdis,
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
            // $(".firstTr1:eq(0)").clone(true)
            //     .find("input").attr("value", "").end()
            //     .find("select").attr("value", "").end()
            //     .appendTo($("#myTbl"));
            var newrow = $("#tr1").clone(true);
            newrow.show();
            $("#myTbl").append(newrow);
        });
        $("#removeRow").click(function() {
            // // ส่วนสำหรับการลบ
            if ($("#myTbl tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                $("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
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
            $(".firstTr1:eq(0)").clone(true)
                .find("input").attr("value", "").end()
                .find("select").attr("value", "").end()
                .appendTo($("#myTbl2"));
        });
        $("#removeRow2").click(function() {
            // // ส่วนสำหรับการลบ
            if ($("#myTbl2 tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                $("#myTbl2 tr:last").remove(); // ลบรายการสุดท้าย
            } else {
                // เหลือ 1 รายการลบไม่ได้
                if ($("#myTbl2 tr").length > 1) {
                    //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                    toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                        timeOut: 5000
                    });
                }
            }
        });
        $(".btnRemoveg2").click(function() {
            // // ส่วนสำหรับการลบ
            if ($("#myTbl2 tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                //$("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                $(this).closest("tr").remove();
            } else {
                // เหลือ 1 รายการลบไม่ได้
                if ($("#myTbl2 tr").length > 1) {
                    //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                    toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                        timeOut: 5000
                    });
                }
            }
        });

        $(this).closest(".abcd").remove();

    });
</script>
@endsection