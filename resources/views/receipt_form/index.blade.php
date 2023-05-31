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
                    <div>{{ $sfielda[request()->get('sfield')] }} Online Form
                        <div class="page-title-subheading">Infinite Brain Online Form Data</div>
                    </div>
                </div>
                <div class="page-title-actions">



                    <div class="d-inline-block dropdown">
                        <a class="btn btn-info export_button" href="#"><i class="fa-solid fa-file-export"></i> Export
                            Excel </a>

                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a>

                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <form method="post" action="" name="delete_all" id="delete_all">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="sfield" value="{{ request()->get('sfield') }}">
                    <table style="width: 100%;" id="Listview"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                <th width="10px"><input type="checkbox" id="check-all" class="flat"></th>
                                <th>Receipt</th>
                                <th>Submission Date</th>
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






    <!-- Edit  Modal -->
    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="EditModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Generate Receipt</h5>
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
                            <strong>Success!</strong> Receipt was generate successfully.
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="EditModalBody">

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="Submission Date">Submission Date:</label>
                                        <input type="text" class="form-control" name="submission_date"
                                            id="AddSubmissiondate" value="" >
                                            <input type="hidden" name="Sfield" id="Sfield" value="{{ $sfield }}">
                                            <input type="hidden" name="fid" id="fid" value="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative  form-group">
                                        <label for="Package">Package:</label>
                                        <input type="text" class="form-control" name="package" id="AddPackage"
                                            value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="firstname">First Name:</label>
                                        <input type="text" class="form-control" name="firstname" id="AddFirstname"
                                            value="" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="lastname">Last Name:</label>
                                        <input type="text" class="form-control" name="lastname" id="AddLastname"
                                            value="" >
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="Nickname">Nick Name:</label>
                                        <input type="text" class="form-control" name="nickname" id="AddNickname"
                                            value="" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="BirthDate">Birth Date:</label>
                                        <input type="text" class="form-control" name="birthdate" id="AddBirthdate"
                                            value="" >
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="School">School Name:</label>
                                        <input type="text" class="form-control" name="school" id="AddSchoolname"
                                            value="" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="Grade">Grade:</label>
                                        <input type="text" class="form-control" name="grade" id="AddGrade"
                                            value="" >
                                    </div>
                                </div>

                            </div>


                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Parent">Name Parent:</label>
                                        <input type="text" class="form-control" name="name_parent" id="AddParent"
                                            value="" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Mobile">Mobile:</label>
                                        <input type="text" class="AddPhone form-control" name="mobile"
                                            id="AddMobile" value="" >
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Parent">Email:</label>
                                        <input type="text" class="form-control" name="email" id="AddEmail"
                                            value="" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Newsf">ข่าวสารการจัดสอบ:</label>
                                        <input type="text" class="form-control" name="newsf"
                                            id="AddNewsf" value="" >
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label for="Name">Street Address:</label>
                                <input type="text" class="form-control" name="address" id="AddAddress" >
                            </div>

                            <div class="form-group">
                                <label for="Postcode">Postcode:</label>
                                <input type="text" class="form-control" name="postcode" id="AddPostcode"
                                    value="" >
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Parent">Contest:</label>
                                        <input type="text" class="form-control" name="contest" id="AddContest"
                                            value="" >
                                    </div>
                                </div>

                            </div>

                            <div class="form-row text-center">
                                <div class="col-md-6">
                                    <label for="รูปภาพผู้สมัคร">รูปภาพผู้สมัคร</label>
                                    <div class="form-group" id="sphoto">

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="หลักฐานการชำระเงิน">หลักฐานการชำระเงิน:</label>
                                    <div class="form-group" id="satt">

                                    </div>
                                    <input type="hidden" id="AddFile" name="file">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitEditForm">Generate Receipt</button>
                        <button type="button" class="btn btn-warning" id="SubmitEditOForm">Edit</button>
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
                        $('form#delete_all').attr('action', '{{ route('receipts_form.destroy_all') }}'); //this fails silently
                        $('form#delete_all').submit();
                    }
                } else {
                    alert("Please Select Record For Delete");
                }

            });

            $(".export_button").click(function() {
                var len = $('input[name="table_records[]"]:checked').length;
                if (len > 0) {
                    if (confirm("Click OK to Export?")) {
                        $('form#delete_all').attr('action', '{{ route('receipts_form.export') }}'); //this fails silently
                        $('form#delete_all').submit();
                    }
                } else {
                    alert("Please Select Record For Export");
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
                        data: 'receipt_status',
                        name: 'receipt_status'
                    },
                    {
                        data: 'receipt_submission_date',
                        name: 'receipt_submission_date'
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





            let id;
            $(document).on('click', '#getEditData', function(e) {
                e.preventDefault();
                $('#SubmitEditForm').css('display','block');

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                id = $(this).data('id');
                $.ajax({
                    url: "receipts_form/edit/" + id,
                    method: 'GET',
                    success: function(res) {
                        console.log(res);
                        //$('#EditModalBody').html(res.html);
                        $('#AddSubmissiondate').val(res.data.submission_date);
                        $('#AddPackage').val(res.data.package);
                        $('#AddContest').val(res.data.contest);
                        $('#AddFirstname').val(res.data.first_name);
                        $('#AddLastname').val(res.data.last_name);
                        $('#AddNickname').val(res.data.nick_name);
                        $('#AddBirthdate').val(res.data.birth_date);
                        $('#AddSchoolname').val(res.data.school_name);
                        $('#AddtDes').val(res.data.description);
                        $('#AddGrade').val(res.data.grade);
                        $('#AddAddress').val(res.data.street_address);
                        //$('#EditAddress2').val(res.data.street_address2);
                        //$('#EditCity').val(res.data.city);
                        //$('#EditProvince').val(res.data.province);
                        $('#AddPostcode').val(res.data.postcode);
                        $('#AddParent').val(res.data.name_parent);
                        $('#AddMobile').val(res.data.mobile);
                        $('#AddEmail').val(res.data.email);
                        $('#AddNewsf').val(res.data.news_form);
                        //alert(res.data.file_re);
                        $('#AddFile').val(res.data.file_re);
                        $('#fid').val(res.data.id);
                        $('#sphoto').html(res.data.photo);
                        $('#satt').html(res.data.file);
                        if (res.data.generate==1) {
                            $('#SubmitEditForm').css('display','none');
                        }
                        //console.log(res.data.photo);
                        $('#EditModal').modal('show');

                    }
                });

            })




            $('#SubmitEditForm').click(function(e) {
                //if (!confirm("Are you sure?")) return;
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                //alert($('#AddFile').val());

                $.ajax({
                    url: "{{ route('receipts_form.store') }}",
                    method: 'post',
                    data: {
                        submission_date: $('#AddSubmissiondate').val(),
                        first_name: $('#AddFirstname').val(),
                        last_name: $('#AddLastname').val(),
                        grade: $('#AddGrade').val(),
                        description: $('#AddPackage').val(),
                        street_address: $('#AddAddress').val(),
                        //street_address2: $('#AddAddress2').val(),
                        //city: $('#AddCity').val(),
                        //province: $('#AddProvince').val(),
                        postcode: $('#AddPostcode').val(),
                        name_parent: $('#AddParent').val(),
                        mobile: $('#AddMobile').val(),
                        package: $('#AddPackage').val(),
                        contest: $('#AddContest').val(),
                        file_att: $('#AddFile').val(),
                        sfield: $('#Sfield').val(),
                        fid: $('#fid').val(),
                        _token: token,
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
                            $('#Listview').DataTable().ajax.reload(null, false );

                            //setTimeout(function() {
                            //$('.alert-success').hide();
                            //   $('#EditModal').modal('hide');
                            //}, 10000);

                        }
                    }
                });
            });


            $('#SubmitEditOForm').click(function(e) {
                //if (!confirm("Are you sure?")) return;
                e.preventDefault();


                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                //alert($('#AddFile').val());

                $.ajax({
                    url: "{{ route('receipts_form.update') }}",
                    method: 'post',
                    data: {
                        submission_date: $('#AddSubmissiondate').val(),
                        first_name: $('#AddFirstname').val(),
                        last_name: $('#AddLastname').val(),
                        nick_name: $('#AddNickname').val(),
                        birth_date: $('#AddBirthdate').val(),
                        grade: $('#AddGrade').val(),
                        //description: $('#AddPackage').val(),
                        street_address: $('#AddAddress').val(),
                        //street_address2: $('#AddAddress2').val(),
                        //city: $('#AddCity').val(),
                        //province: $('#AddProvince').val(),
                        postcode: $('#AddPostcode').val(),
                        school_name: $('#AddSchoolname').val(),
                        name_parent: $('#AddParent').val(),
                        mobile: $('#AddMobile').val(),
                        email: $('#AddEmail').val(),
                        package: $('#AddPackage').val(),
                        contest: $('#AddContest').val(),
                        news_form: $('#AddNewsf').val(),
                        //file_att: $('#AddFile').val(),
                        sfield: $('#Sfield').val(),
                        fid: $('#fid').val(),
                        _token: token,
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
                            $('#Listview').DataTable().ajax.reload(null, false );

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
                    url: "receipts_form/destroy/" + rowid,
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
