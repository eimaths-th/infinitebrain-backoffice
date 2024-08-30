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
                    <div>
                        Lotto
                        <div class="page-title-subheading">{{ date('d-m-Y') }}</div>

                    </div>

                </div>
                <div class="page-title-actions">

                    <div class="d-inline-block dropdown">
                        {{-- <a href="{{ route('lotto.income.create') }}" class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton3"><i class="fa fa-plus"></i> เพิ่มรายการ</a> --}}


                    </div>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <p>ผลสลากกินแบ่ง วันที่ {{ $result->date ?? ''}} </P>
                @if ($result)
                    <p>รางวัลที่ 1 {{ $result->{'1st'} }}</P>
                    <p> เลขหน้า 3 ตัว {{ $result->{'3front_1'} }} , {{ $result->{'3front_2'} }} </p>
                    <p> เลขท้าย 3 ตัว {{ $result->{'3back_1'} }} , {{ $result->{'3back_2'} }} </p>
                    <p> เลขท้าย 2 ตัว {{ $result->{'2numbers'} }}</p>
                @else
                    <p>กำลังรอผล</p>
                @endif
            </div>
            <div class="card-body">
                <form method="post" action="#" name="delete_all" id="delete_all">
                    @csrf
                    @method('DELETE')
                    <table style="width: 100%;" id="Listview4"
                        class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                        <thead>
                            <tr>
                                {{-- <th width="10px"><input type="checkbox" id="check-all" class="flat"></th> --}}
                                <th width="1%">No</th>
                                <th>date</th>
                                <th>name</th>
                                <th>numbers</th>
                                <th>type</th>
                                <th>bet</th>
                                <th>status</th>
                                <th>win/lose</th>
                                <th>result</th>
                                {{-- <th>remain</th> --}}
                                <th width="1%" class="text-nowrap">Action</th>
                            </tr>
                        </thead>

                    </table>
                </form>



            </div>
        </div>

    </div>
@endsection


{{-- @include('lottoes.income-create') --}}
@include('lottoes.scripts-report')
