@extends('layouts.main_template')
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
                        <a class="btn btn-success" href="{{ route('users.create') }}"><i class="fa fa-plus"></i> Create New User</a>
                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete All</a>
                        
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

    
    <div class="fade modal" tabindex="-1" role="dialog"  aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                            <input type="text" name="phone" class="form-control input-sm">
                        </div>
                        <div class="form-group">
                            <label for="dob">DOB</label>
                            <input type="date" name="dob" class="form-control input-sm">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-save">Save</button>
                        <button type="button" class="btn btn-primary btn-update">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>



    @vite(['resources/js/Users.js'])
@endsection


