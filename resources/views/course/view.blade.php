@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                        <i class="pe-7s-network icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        Study Course
                        <div class="page-title-subheading">Infinite Brain Study Course Data</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New
                            Course</a>
                        <a class="btn btn-danger delete_all_button" href="#"><i class="fa fa-trash"></i> Delete
                            All</a>

                    </div>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                ( {{ $course[0]->student_code }} ) {{ $course[0]->student_name }} {{ $course[0]->student_lastname }}
                <table style="width: 100%;" id="Listview"
                    class="table table-striped table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            <th>School Grade</th>
                            <th></th>
                            <th colspan="12" class="text-center">( {{ $course[0]->student_grade }} )</th>

                        </tr>
                    </thead>
                    <tbody>

                        {!! $levels !!}
                        {{-- @foreach ($levels as $key)
                            <tr>
                                <td>{{ $key->title }}</td>
                                <td align="center">{{ $key->level_sort }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach --}}


                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2" align="center">Year</td>
                            @for ($i = $year; $i <= $last_year; $i++)
                                <td align="center" colspan="{{ $year_span[$i] }}">{{ $i }}</td>
                            @endfor

                        </tr>
                        <tr>
                            <td colspan="2" align="center">Month</td>

                            @foreach ($months as $key)
                                <td align="center">{{ $key }}</td>
                            @endforeach
                        </tr>
                    </tfoot>

                </table>

            </div>
        </div>

    </div>
@endsection

@section('script')
@endsection
