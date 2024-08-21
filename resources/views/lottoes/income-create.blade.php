@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    <div>Lotto
                        <div class="page-title-subheading">{{ date('d-m-Y') }}</div>
                    </div>
                </div>

            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <div class="col-lg-1">
                    <select name="number_of_item" id="numberOfItem" class="form-control mb-5">
                        @for ($i = 5; $i <= 50; $i += 5)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor

                    </select>
                </div>

                <table style="width: 100%;" id="Listview4"
                    class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            {{-- <th width="10px"><input type="checkbox" id="check-all" class="flat"></th> --}}
                            <th width="1%">No</th>
                            <th>name</th>
                            <th>numbers</th>
                            <th>type</th>
                            <th>value</th>
                            <th>status</th>
                            <th>remain</th>
                            <th>note</th>
                            <th width="1%" class="text-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody id="newtr">

                    </tbody>
                </table>
                <button class="btn btn-success" id="all-submitBtn">submit all</button>

            </div>
        </div>

    </div>
@endsection

@include('lottoes.scripts-income')
