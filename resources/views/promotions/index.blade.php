@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
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
                        <i class="pe-7s-ticket icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        Promotion
                        <div class="page-title-subheading">Promotion master data for school receipts</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New Promotion</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card" id="x_content">
            <div class="card-body">
                <table style="width: 100%;" id="Listview"
                    class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            <th width="80px">ID</th>
                            <th>Name</th>
                            <th width="120px">Status</th>
                            <th width="180px">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="CreateModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Add Promotion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
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
                        <div class="form-group">
                            <label for="AddName">Promotion Name:</label>
                            <input type="text" class="form-control" name="name" id="AddName" value="">
                        </div>
                        <div class="form-group">
                            <label for="AddIsActive">Status:</label>
                            <select name="is_active" id="AddIsActive" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitCreateForm">Save</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="EditModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Promotion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
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
                        <div class="form-group">
                            <label for="EditName">Promotion Name:</label>
                            <input type="text" class="form-control" name="name" id="EditName" value="">
                        </div>
                        <div class="form-group">
                            <label for="EditIsActive">Status:</label>
                            <select name="is_active" id="EditIsActive" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var table = $('#Listview').DataTable({
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
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            function ShowErrors(errors) {
                $('.alert-danger').html('');
                $.each(errors, function(key, value) {
                    $('.alert-danger').show();
                    $('.alert-danger').append('<strong><li>' + value + '</li></strong>');
                });
            }

            function ResetAlerts() {
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
            }

            $(document).on('click', '#CreateButton', function(e) {
                e.preventDefault();
                ResetAlerts();
                $('#CreateModal').modal('show');
            });

            let id;
            $(document).on('click', '#getEditData', function(e) {
                e.preventDefault();
                ResetAlerts();

                id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/edit') }}/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#EditName').val(res.data.name);
                        $('#EditIsActive').val(res.data.is_active);
                        $('#EditModal').modal('show');
                    }
                });
            });

            $('#SubmitCreateForm').click(function(e) {
                e.preventDefault();
                ResetAlerts();

                $.ajax({
                    url: "{{ route('promotions.store') }}",
                    method: 'post',
                    data: {
                        name: $('#AddName').val(),
                        is_active: $('#AddIsActive').val(),
                    },
                    success: function(result) {
                        if (result.errors) {
                            ShowErrors(result.errors);
                        } else {
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $('.form').trigger('reset');
                            $('#CreateModal').modal('hide');
                        }
                    }
                });
            });

            $('#SubmitEditForm').click(function(e) {
                e.preventDefault();
                ResetAlerts();

                $.ajax({
                    url: "{{ url('promotions/save') }}/" + id,
                    method: 'PUT',
                    data: {
                        name: $('#EditName').val(),
                        is_active: $('#EditIsActive').val(),
                    },
                    success: function(result) {
                        if (result.errors) {
                            ShowErrors(result.errors);
                        } else {
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $('#EditModal').modal('hide');
                        }
                    }
                });
            });

            $(document).on('click', '.btn-toggle-status', function(e) {
                e.preventDefault();

                if (!confirm("Update promotion status?")) {
                    return;
                }

                let promotion_id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/toggle-status') }}/" + promotion_id,
                    method: 'PUT',
                    success: function(result) {
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                    }
                });
            });
        });
    </script>
@endsection
