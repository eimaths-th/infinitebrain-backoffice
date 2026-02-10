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
                        <i class="pe-7s-user icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>Employees
                        <div class="page-title-subheading">Infinite Brain Employee Data</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New
                            User</a>
                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a>

                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <!-- Filters Section -->
                <div class="card mb-3 bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fa fa-filter me-2"></i>ตัวกรองข้อมูล
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filter-department" class="form-label fw-bold">แผนก</label>
                                <select class="form-select" id="filter-department">
                                    <option value="">-- ทั้งหมด --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter-employee-type" class="form-label fw-bold">ประเภทพนักงาน</label>
                                <select class="form-select" id="filter-employee-type">
                                    <option value="">-- ทั้งหมด --</option>
                                    <option value="fulltime">Full-time</option>
                                    <option value="parttime">Part-time</option>
                                    <option value="contract">Contract</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter-status" class="form-label fw-bold">สถานะ</label>
                                <select class="form-select" id="filter-status">
                                    <option value="">-- ทั้งหมด --</option>
                                    <option value="active">ใช้งาน</option>
                                    <option value="disabled">ปิดใช้งาน</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary w-100" id="clear-filters">
                                    <i class="fa fa-times me-1"></i>ล้างตัวกรอง
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('users.destroy_all') }}" name="delete_all" id="delete_all">
                    @csrf
                    @method('DELETE')
                    <table style="width: 100%;" id="Listview"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                <th width="10px"><input type="checkbox" id="check-all" class="flat"></th>
                                <th>No</th>
                                <th>รหัส</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>Email</th>
                                {{-- <th>วันเกิด</th> --}}
                                <th>แผนก</th>
                                <th>ตำแหน่ง</th>
                                <th>Tags</th>
                                <th>Role</th>
                                <th>สถานะ</th>
                                <th width="120px">Action</th>
                            </tr>
                        </thead>

                    </table>
                </form>



            </div>
        </div>

    </div>

    <!-- Create Modal -->
    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="CreateModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Add Employee</h5>
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
                            <!-- Form loaded via AJAX -->
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
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button></button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="display: none;">
                            <strong>Success!</strong> Users was edit successfully.
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="EditModalBody">

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
                ajax: {
                    url: '',
                    data: function(d) {
                        d.department = $('#filter-department').val();
                        d.employee_type = $('#filter-employee-type').val();
                        d.status = $('#filter-status').val();
                    }
                },
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: '<div class="spinner"></div>'
                },
                aaSorting: [
                    [1, "desc"]
                ],
                iDisplayLength: 15,
                lengthMenu: [15, 25, 50, 75, 100],
                stateSave: false,
                sPaginationType: "full_numbers",
                dom: 'T<"clear">lfrtip',
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    // {
                    //     data: 'birth_date',
                    //     name: 'birth_date'
                    // },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'position',
                        name: 'position'
                    },
                    {
                        data: 'tags',
                        name: 'tags',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'level',
                        name: 'level'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Filter change events
            $('#filter-department, #filter-employee-type, #filter-status').on('change', function() {
                table.ajax.reload();
            });

            // Clear filters button
            $('#clear-filters').on('click', function() {
                $('#filter-department').val('');
                $('#filter-employee-type').val('');
                $('#filter-status').val('');
                table.ajax.reload();
            });



            $(document).on('click', '#CreateButton', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $.ajax({
                    url: "{{ route('users.create') }}",
                    method: 'GET',
                    success: function(res) {
                        $('#CreateModalBody').html(res.html);
                        // Re-initialize Flatpickr for AJAX-loaded date inputs
                        flatpickr('#CreateModalBody .datepicker', {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd/m/Y',
                            locale: 'th',
                            allowInput: true
                        });
                        $('#CreateModal').modal('show');
                    }
                });
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
                    url: "users/edit/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#EditModalBody').html(res.html);
                        // Re-initialize Flatpickr for AJAX-loaded date inputs
                        flatpickr('#EditModalBody .datepicker', {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd/m/Y',
                            locale: 'th',
                            allowInput: true
                        });
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

                var formData = $('#CreateModal form').serialize();
                // Add token if not included (serialize includes _token if input exists, but csrf setup handles headers)
                // However, our controller expects params.

                $.ajax({
                    url: "{{ route('users.store') }}",
                    method: 'post',
                    data: formData,
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
                            $('.alert-success').html('<strong><li>' + result.success +
                                '</li></strong>');
                            $('#Listview').DataTable().ajax.reload();
                            $('#CreateModal form').trigger('reset');
                            setTimeout(function() {
                                $('.alert-success').hide();
                                $('#CreateModal').modal('hide');
                            }, 1500);

                        }
                    }
                });
            });

            $('#SubmitEditForm').click(function(e) {
                if (!confirm("Are you sure?")) return;
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                var formData = $('#EditModal form').serialize();

                $.ajax({
                    url: "users/save/" + id,
                    method: 'PUT',
                    data: formData,

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
                            $('.alert-success').html('<strong><li>' + result.success +
                                '</li></strong>');
                            $('#Listview').DataTable().ajax.reload();
                            setTimeout(function() {
                                $('.alert-success').hide();
                                $('#EditModal').modal('hide');
                            }, 1500);
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
                    url: "users/destroy/" + rowid,
                    data: {
                        _method: 'delete',
                        _token: token
                    },
                    success: function(data) {

                        toastr.success(data.message, {
                            timeOut: 5000
                        });

                        $('#Listview').DataTable().ajax.reload();
                        if (data.success) {
                            table.row(el.parents('tr'))
                                .remove()
                                .draw();
                        }
                    }
                }); //end ajax
            })

            $(document).on('click', '.btn-toggle-disable', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var btn = $(this);

                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    url: "users/toggle-disable/" + id,
                    data: {
                        _token: token
                    },
                    success: function(data) {
                        if (data.success) {
                            toastr.success(data.message, {
                                timeOut: 3000
                            });
                            $('#Listview').DataTable().ajax.reload();
                        } else {
                            toastr.error(data.message, {
                                timeOut: 3000
                            });
                        }
                    },
                    error: function(xhr) {
                        toastr.error('เกิดข้อผิดพลาด', {
                            timeOut: 3000
                        });
                    }
                });
            })

            // Clear modal body on close to prevent ID conflicts
            $('#CreateModal').on('hidden.bs.modal', function() {
                $('#CreateModalBody').html('');
            });
            $('#EditModal').on('hidden.bs.modal', function() {
                $('#EditModalBody').html('');
            });
        })
    </script>
@endsection
