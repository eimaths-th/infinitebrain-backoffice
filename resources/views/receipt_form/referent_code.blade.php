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
@endsection

@section('content')
    @if ($message = Session::get('error'))
        <div class="alert alert-danger mb-5">
            <p>{{ $message }}</p>
        </div>
    @endif
    <div class="row">

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fas fa-mortar-board icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>Referent Code
                        <div class="page-title-subheading">Infinite Brain Referent Code Data</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">

                        <button type="button" id="generate_code" onclick="formModal('{{ route('generate_code') }}')"
                            class="btn btn-primary">Generate code</button>
                        <script>
                            function referent_codeSave() {
                                var route = $('#route').val();
                                var referent_name = $('#referent_name').val();
                                var comments = $('#comments').val();
                                $.ajax({
                                    url: "{{ route('referent_code.save') }}",
                                    type: "POST",
                                    data: {
                                        route: route,
                                        referent_name: referent_name,
                                        comments: comments,
                                        _token: "{{ csrf_token() }}",
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            toastr.success(response.message, {
                                                timeOut: 5000
                                            });
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 5000);
                                        } else {
                                            toastr.error(response.message, {
                                                timeOut: 5000
                                            });
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.log(error);
                                    }
                                });

                            }
                        </script>

                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                {{-- random code 10 characters --}}

                <form method="post" action="#" name="delete_all" id="delete_all">
                    @csrf

                    <table style="width: 100%;" id="Listview"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                {{-- <th width="10px"><input type="checkbox" id="check-all" class="flat"></th> --}}
                                <th width="1%" align="center">#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Explain</th>
                                <th>Created by</th>
                                <th>Created at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referent_code as $key => $value)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $value->referent_code }}</td>
                                    <td>{{ $value->referent_name }}</td>
                                    <td>{{ $value->comments }}</td>
                                    <td>{{ $value->user->name }}</td>
                                    <td>{{ $value->created_at }}</td>
                                </tr>
                            @endforeach
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
@endsection

@section('script')
    <script>
        var table = $('#Listview').DataTable({
            // serverSide: true,
            // processing: true,
            lengthMenu: [10, 20, 50, 75, 100],
        });

        $(document).on('click', '.f5', function(e) {
            e.preventDefault();
            window.open(event.target.href);
            window.location.reload();
            event.preventDefault();
            console.log(window.location);
        });
    </script>
@endsection
