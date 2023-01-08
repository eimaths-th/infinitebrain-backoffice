@extends('layouts.main_template')

@section('style')
    <script src="https://code.jquery.com/jquery-3.5.1.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>

    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
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
                    <div>Employee
                        <div class="page-title-subheading">Infinite Brain Employee Data</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <!--<button type="button" data-toggle="tooltip" title="Example Tooltip" data-placement="bottom"
                                            class="btn-shadow mr-3 btn btn-dark">
                                            <i class="fa fa-star"></i>
                                        </button>-->
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-success" href="{{ route('users.create') }}"><i class="fa fa-plus"></i> Create New
                            User</a>
                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a>

                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <form method="post" action="{{ route('users.destroy_all') }}" name="delete_all" id="delete_all">
                    @csrf
                    @method('DELETE')
                    <table style="width: 100%;" id="Listview"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                <th width="10px"><input type="checkbox" id="check-all" class="flat"></th>
                                <th>No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Level</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>

                    </table>
                </form>



            </div>
        </div>

    </div>


    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" class="form-control input-sm">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" name="email" class="form-control input-sm">
                        </div>
                        <div class="form-group">
                            <label for="dob">DOB</label>
                            <input type="text" name="level" class="form-control input-sm">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-save">Save</button>
                        <button type="button" class="btn btn-primary btn-update">Update</button>
                        <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
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
            var modal = $('.modal')
            var form = $('.form')
            var btnAdd = $('.add'),
                btnSave = $('.btn-save'),
                btnUpdate = $('.btn-update');

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
                aaSorting: [
                    [0, "desc"]
                ],
                iDisplayLength: 15,
                lengthMenu: [15, 25, 50, 75, 100],
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
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'level',
                        name: 'level'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ]
            });

            btnAdd.click(function() {
                modal.modal()
                form.trigger('reset')
                modal.find('.modal-title').text('Add New')
                btnSave.show();
                btnUpdate.hide()
            })

            btnSave.click(function(e) {
                e.preventDefault();
                var data = form.serialize()
                console.log(data)
                $.ajax({
                    type: "POST",
                    url: "",
                    data: data + '&_token=' + token,
                    success: function(data) {
                        if (data.success) {
                            table.draw();
                            form.trigger("reset");
                            modal.modal('hide');
                        } else {
                            alert('Delete Fail');
                        }
                    }
                }); //end ajax
            })


            $(document).on('click', '.btn-edit', function() {
                btnSave.hide();
                btnUpdate.show();


                modal.find('.modal-title').text('Update Record')
                modal.find('.modal-footer button[type="submit"]').text('Update')

                var rowData = table.row($(this).parents('tr')).data()

                form.find('input[name="id"]').val(rowData.id)
                form.find('input[name="name"]').val(rowData.name)
                form.find('input[name="email"]').val(rowData.email)
                form.find('input[name="level"]').val(rowData.level)

                modal.modal('show');
            })

            btnUpdate.click(function() {
                if (!confirm("Are you sure?")) return;
                var formData = form.serialize() + '&_method=PUT&_token=' + token
                var updateId = form.find('input[name="id"]').val()
                $.ajax({
                    type: "POST",
                    url: "/" + updateId,
                    data: formData,
                    success: function(data) {
                        if (data.success) {
                            table.draw();
                            modal.modal('hide');
                        }
                    }
                }); //end ajax
            })


            $(document).on('click', '.btn-delete', function() {
                if (!confirm("Are you sure?")) return;

                var rowid = $(this).data('rowid')
                var el = $(this)
                if (!rowid) return;


                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    url: "/users/destroy/" + rowid,
                    data: {
                        _method: 'delete',
                        _token: token
                    },
                    success: function(data) {
                        if (data.success) {
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
