@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="http://malsup.github.io/jquery.blockUI.js"></script>
    <script src="https://rawgit.com/RobinHerbots/Inputmask/5.x/dist/jquery.inputmask.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
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
                    <div>{{ $sfielda[request()->get('sfield')] }} Receipts
                        <div class="page-title-subheading">Infinite Brain Receipt Data</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-info" data-toggle="modal" data-target=".bd-example-modal-lg" id="ImportButton"><i
                                class="fa fa-file-excel"></i> Import Excel
                        </a>
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New
                            Receipt</a>
                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a>

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
                                <th>Submission Date</th>
                                <th>Voucher Number</th>
                                <th>Customer Code</th>
                               
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Parent Name</th>
                                <th>Mobile</th>
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
                                <select class="form-control" id="AddField" name="sfield" required>
                                    @foreach ($sfielda as $key=>$value)
                                    <option value="{{ $key }}"  {{ request()->get('sfield') == $key ? 'selected' : '' }}> {{ $value }} </option>
                                @endforeach
                                </select>
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
    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="CreateModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Add Receipt</h5>
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
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Submission Date">Submission Date:</label>
                                    <input type="text" class="AddDate form-control" name="submission_date"
                                        id="AddSubmissiondate" value="">
                                        <input type="hidden" name="Sfield" id="Sfield" value="{{ $sfield }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative  form-group">
                                    <label for="Package">Package:</label>
                                    <input type="text" class="form-control" name="package" id="AddPackage"
                                        value="">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="firstname">First Name:</label>
                                    <input type="text" class="form-control" name="firstname" id="AddFirstname"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="lastname">Last Name:</label>
                                    <input type="text" class="form-control" name="lastname" id="AddLastname"
                                        value="">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Grade">Grade:</label>
                                    <input type="text" class="form-control" name="grade" id="AddGrade"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Des">Description:</label>
                                    <input type="text" class="form-control" name="description" id="AddDes"
                                        value="">
                                </div>
                            </div>
                        </div>

                        <!--<div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="City">City:</label>
                                    <input type="text" class="form-control" name="city" id="AddCity"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Province">Province:</label>
                                    <input type="text" class="form-control" name="province" id="AddProvince"
                                        value="">
                                </div>
                            </div>
                        </div>-->
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Parent">Name Parent:</label>
                                    <input type="text" class="form-control" name="name_parent" id="AddParent"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Mobile">Mobile:</label>
                                    <input type="text" class="AddPhone form-control" name="mobile" id="AddMobile"
                                        value="">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Postcode">Postcode:</label>
                            <input type="text" class="form-control" name="postcode" id="AddPostcode" value="">
                        </div>

                        <div class="form-group">
                            <label for="Name">Street Address:</label>
                            <textarea class="form-control" name="address" id="AddAddress"></textarea>
                        </div>
                        <!--<div class="form-group">
                            <label for="Name">Street Address 2:</label>
                            <textarea class="form-control" name="address2" id="AddAddress2"></textarea>
                        </div>-->

                        <div class="form-group">
                            <label for="File">File:</label>
                            <input type="text" class="form-control" id="AddFile" name="file" value="">
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
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Receipt</h5>
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

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="ReceiptNumber">Voucher Number:</label>
                                        <input type="text" class="form-control" name="receipt_number" id="receipt_number"
                                             value="" readonly> 
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative  form-group">
                                        <label for="ReceiptCNumber">Customer Code:</label>
                                        <input type="text" class="form-control" name="customer_code" id="customer_code"
                                            value="" readonly>
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
                                    <div class="position-relative  form-group">
                                        <label for="Package">Package:</label>
                                        <input type="text" class="form-control" name="package" id="EditPackage"
                                            value="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="firstname">First Name:</label>
                                        <input type="text" class="form-control" name="firstname" id="EditFirstname"
                                            value="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="lastname">Last Name:</label>
                                        <input type="text" class="form-control" name="lastname" id="EditLastname"
                                            value="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="Grade">Grade:</label>
                                        <input type="text" class="form-control" name="grade" id="EditGrade"
                                            value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="Postcode">Postcode:</label>
                                    <input type="text" class="form-control" name="postcode" id="EditPostcode"
                                        value="">
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


                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Parent">Name Parent:</label>
                                        <input type="text" class="form-control" name="name_parent" id="EditParent"
                                            value="">
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
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Des">Description:</label>
                                    <textarea class="form-control" name="drescription" id="EditDes"></textarea>
                                </div>
                            </div>
                           
                            <div class="form-group">
                                <label for="Name">Street Address:</label>
                                <textarea class="form-control" name="address" id="EditAddress"></textarea>
                            </div>
                            <!--<div class="form-group">
                                <label for="Name">Street Address 2:</label>
                                <textarea class="form-control" name="address2" id="EditAddress2"></textarea>
                            </div>-->

                            <div class="form-group">
                                <label for="File">File:</label>
                                <input type="text" class="form-control" id="EditFile" name="file"
                                    value="">
                            </div>
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
@endsection

@section('script')
    <script>
        $(document).ready(function() {

            $(".AddDate").datepicker({
                dateFormat: "yy-mm-dd"
            });

            $(".AddDate").inputmask("9999-99-99");
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
                        data: 'receipt_submission_date',
                        name: 'receipt_submission_date'
                    },
                    {
                        data: 'receipt_number',
                        name: 'receipt_number'
                    },
                    {
                        data: 'receipt_customer_number',
                        name: 'receipt_customer_number'
                    },
                    {
                        data: 'receipt_firstname',
                        name: 'receipt_firstname'
                    },
                    {
                        data: 'receipt_lastname',
                        name: 'receipt_lastname'
                    },
                    {
                        data: 'receipt_parent_name',
                        name: 'receipt_parent_name'
                    },
                    {
                        data: 'receipt_mobile',
                        name: 'receipt_mobile'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ]
            });




            $(document).on('click', '#CreateButton', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('#CreateModal').modal('show');
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
                    url: "receipts/edit/" + id,
                    method: 'GET',
                    success: function(res) {
                        //console.log(res);
                        //$('#EditModalBody').html(res.html);
                        $('#receipt_number').val(res.data.receipt_number);
                        $('#customer_code').val(res.data.receipt_customer_number);
                        $('#EditSubmissiondate').val(res.data.submission_date);
                        $('#EditPackage').val(res.data.package);
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
                        $('#EditFile').val(res.data.file_att);
                        $('#EditModal').modal('show');

                    }
                });

            })




            // Create product Ajax request.
            $('#SubmitCreateForm').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $.ajax({
                    url: "{{ route('receipts.store') }}",
                    method: 'post',
                    data: {
                        submission_date: $('#AddSubmissiondate').val(),
                        first_name: $('#AddFirstname').val(),
                        last_name: $('#AddLastname').val(),
                        grade: $('#AddGrade').val(),
                        description: $('#AddDes').val(),
                        street_address: $('#AddAddress').val(),
                        //street_address2: $('#AddAddress2').val(),
                        //city: $('#AddCity').val(),
                        //province: $('#AddProvince').val(),
                        postcode: $('#AddPostcode').val(),
                        name_parent: $('#AddParent').val(),
                        mobile: $('#AddMobile').val(),
                        package: $('#AddPackage').val(),
                        file_att: $('#AddFile').val(),
                        sfield: $('#Sfield').val(),
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
    </script>
@endsection
