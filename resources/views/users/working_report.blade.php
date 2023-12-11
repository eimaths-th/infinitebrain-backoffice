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
    <link href="../assets/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" />
    <link href="../assets/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
    <style>
        @media screen and (max-width: 100px) {
            .screen-auto {
                max-width: 100%;
                height: auto;
            }
        }

        .screen-auto {
            max-width: 800;
            height: 200;
        }
    </style>
@endsection

@section('content')


    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="fa fa-home
                            icon-gradient bg-happy-itmeo">
                    </i>
                </div>
                <div>Wellcome
                    <div class="page-title-subheading">
                        Infinite Brain Dashboard
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- <div class="main-card mb-3 card">
        <div class="card-header-tab card-header-tab-animation card-header">
            <div class="card-header-title">
                <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                Current Month Report <br>{{ date('F - Y') }}
            </div>
        </div>

        <div class="g-0 row">
            @foreach ($rec_times as $status => $count)
                @if ($status != 'วันหยุด')
                    @php
                        $total = 'Total';
                        $class = 'secondary';
                        switch ($status) {
                            case 'ปกติ':
                                $class = 'success';
                                break;
                            case 'สาย':
                            case 'ออกก่อน':
                            case 'ลาครึ่งวัน':
                            case 'ไม่แสกนออก':
                            case 'ไม่แสกนเข้า':
                                $class = 'warning';
                                break;
                            case 'OT':
                            case 'ชดเชย':
                                $class = 'primary';
                                break;
                            case 'ขาดงาน':
                                $class = 'danger';
                                break;
                            case 'ลาป่วย':
                                $class = 'danger';
                                $total = 'คงเหลือ ' . 30 - $count . 'ครั้ง';
                                break;
                            case 'ลาพักร้อน':
                                $class = 'danger';

                                $total = 'คงเหลือ ' . 6 - $count . 'ครั้ง';
                                break;
                            case 'ลากิจ':
                                $class = 'danger';
                                // Add more cases as needed
                                $total = 'คงเหลือ ' . 2 - $count . 'ครั้ง';
                                break;
                            default:
                                $class = 'secondary'; // Default color
                        }

                    @endphp
                    <div class="col-md-2">
                        <div class="pt-0 pb-0 card-body">
                            <ul class="list-group list-group-flush">

                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-outer">
                                            <div class="widget-content-wrapper">
                                                <div class="widget-content-left">
                                                    <div class="widget-heading text-{{ $class }}">
                                                        {{ $status }}
                                                    </div>
                                                    <div class="widget-subheading">{{ $total }}</div>
                                                </div>
                                                <div class="widget-content-right">
                                                    <div class="widget-heading">
                                                        {{ $count }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div> --}}

    {{-- <div class="main-card mb-3 card">
        <div class="card-header-tab card-header-tab-animation card-header">
            <div class="card-header-title">
                <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                Current Year Report <br>{{ date('Y') }}
            </div>
        </div>
        <div class="g-0 row">

            @foreach ($rec_times_year as $status => $count)
                @if ($status != 'วันหยุด')
                    @php
                        $total = 'Total';
                        $class = 'secondary';
                        switch ($status) {
                            case 'ปกติ':
                                $class = 'success';
                                break;
                            case 'สาย':
                            case 'ออกก่อน':
                            case 'ลาครึ่งวัน':
                            case 'ไม่แสกนออก':
                            case 'ไม่แสกนเข้า':
                                $class = 'warning';
                                break;
                            case 'OT':
                            case 'ชดเชย':
                                $class = 'primary';
                                break;
                            case 'ขาดงาน':
                                $class = 'danger';
                                break;
                            case 'ลาป่วย':
                                $class = 'danger';
                                $total = 'คงเหลือ ' . 30 - $count . 'ครั้ง';
                                break;
                            case 'ลาพักร้อน':
                                $class = 'danger';

                                $total = 'คงเหลือ ' . 6 - $count . 'ครั้ง';
                                break;
                            case 'ลากิจ':
                                $class = 'danger';
                                // Add more cases as needed
                                $total = 'คงเหลือ ' . 2 - $count . 'ครั้ง';
                                break;
                            default:
                                $class = 'secondary'; // Default color
                        }

                    @endphp
                    <div class="col-md-2">
                        <div class="pt-0 pb-0 card-body">
                            <ul class="list-group list-group-flush">

                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-outer">
                                            <div class="widget-content-wrapper">
                                                <div class="widget-content-left">
                                                    <div class="widget-heading text-{{ $class }}">
                                                        {{ $status }}</div>
                                                    <div class="widget-subheading">{{ $total }}</div>
                                                </div>
                                                <div class="widget-content-right">
                                                    <div class="widget-heading">
                                                        {{ $count }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div> --}}

    <div class="main-card mb-3 card">
        <div class="card-header-tab card-header-tab-animation card-header">
            <div class="card-header-title">
                <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                Current Month Report <br>{{ date('F - Y') }}
            </div>
        </div>
        <div class="g-0 row">
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">

                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-success">
                                                ปกติ
                                            </div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-heading text-success">
                                                {{ $rec_times['ปกติ'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-warning">
                                                สาย
                                            </div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-heading text-warning">
                                                {{ $rec_times['สาย'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-primary">OT</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-primary">
                                                {{ $rec_times['OT'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-primary">ชดเชย</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-primary">
                                                {{ $rec_times['ชดเชย'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-warning">ออกก่อน</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times['ออกก่อน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-warning">ลาครึ่งวัน</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times['ลาครึ่งวัน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-warning">ไม่แสกนออก</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times['ไม่แสกนออก'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-warning">ไม่แสกนเข้า</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times['ไม่แสกนเข้า'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ขาดงาน</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times['ขาดงาน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ลาป่วย</div>
                                            <div class="widget-subheading">
                                                @php
                                                    $sumA = 30;
                                                    if (isset($rec_times['ลาป่วย'])) {
                                                        $sumA = 30 - $rec_times['ลาป่วย'];
                                                    }
                                                @endphp
                                                คงเหลือ {{ $sumA }} ครั้ง
                                            </div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times['ลาป่วย'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ลาพักร้อน</div>
                                            <div class="widget-subheading">
                                                @php
                                                    $sumb = 6;
                                                    if (isset($rec_times['ลาพักร้อน'])) {
                                                        $sumb = 6 - $rec_times['ลาพักร้อน'];
                                                    }
                                                @endphp
                                                คงเหลือ {{ $sumb }} ครั้ง
                                            </div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times['ลาพักร้อน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ลากิจ</div>
                                            <div class="widget-subheading">
                                                @php
                                                    $sumc = 3;
                                                    if (isset($rec_times['ลากิจ'])) {
                                                        $sumc = 3 - $rec_times['ลากิจ'];
                                                    }
                                                @endphp
                                                คงเหลือ {{ $sumc }} ครั้ง
                                            </div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times['ลากิจ'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
    <div class="main-card mb-3 card">
        <div class="card-header-tab card-header-tab-animation card-header">
            <div class="card-header-title">
                <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                Current Year Report <br>{{ date('Y') }}
            </div>
        </div>
        <div class="g-0 row">
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">

                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-success">
                                                ปกติ
                                            </div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-heading text-success">
                                                {{ $rec_times_year['ปกติ'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-warning">
                                                สาย
                                            </div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-heading text-warning">
                                                {{ $rec_times_year['สาย'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-primary">OT</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-primary">
                                                {{ $rec_times_year['OT'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-primary">ชดเชย</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-primary">
                                                {{ $rec_times_year['ชดเชย'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-warning">ออกก่อน</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times_year['ออกก่อน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-warning">ลาครึ่งวัน</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times_year['ลาครึ่งวัน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-warning">ไม่แสกนออก</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times_year['ไม่แสกนออก'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-warning">ไม่แสกนเข้า</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning">
                                                {{ $rec_times_year['ไม่แสกนเข้า'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ขาดงาน</div>
                                            <div class="widget-subheading">Total</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times_year['ขาดงาน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ลาป่วย</div>
                                            <div class="widget-subheading">
                                                @php
                                                    $sumA = 30;
                                                    if (isset($rec_times_year['ลาป่วย'])) {
                                                        $sumA = 30 - $rec_times_year['ลาป่วย'];
                                                    }
                                                @endphp
                                                คงเหลือ {{ $sumA }} ครั้ง
                                            </div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times_year['ลาป่วย'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <div class="pt-0 pb-0 card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ลาพักร้อน</div>
                                            <div class="widget-subheading">
                                                @php
                                                    $sumb = 6;
                                                    if (isset($rec_times_year['ลาพักร้อน']) ) {
                                                        $sumb = 6 - $rec_times_year['ลาพักร้อน'];
                                                    }
                                                @endphp
                                                คงเหลือ {{ $sumb }} ครั้ง
                                            </div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times_year['ลาพักร้อน'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="widget-heading text-nowrap text-danger">ลากิจ</div>
                                            <div class="widget-subheading">
                                                @php
                                                    $sumc = 3;
                                                    if (isset($rec_times_year['ลากิจ']) ) {
                                                        $sumc = 3 - $rec_times_year['ลากิจ'];
                                                    }
                                                @endphp
                                                คงเหลือ {{ $sumc }} ครั้ง
                                            </div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-danger">
                                                {{ $rec_times_year['ลากิจ'] ?? 0 }}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script src="../assets/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
@endsection
