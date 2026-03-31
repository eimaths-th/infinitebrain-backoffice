@extends('layouts.main_template')

@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
@endsection

@section('content')
<div class="row">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-graph2 icon-gradient bg-tempting-azure"></i>
                </div>
                <div>Source Report (รายงานแหล่งที่มา)
                    <div class="page-title-subheading">รายงานสรุปข้อมูลแหล่งที่มาของลูกค้า สำหรับระบบใบเสร็จโรงเรียน</div>
                </div>
            </div>
            
            <div class="page-title-actions">
                <div class="d-inline-block">
                    <label class="mr-2">Filter Source:</label>
                    <select id="source_filter" class="form-control" style="width: 250px; display: inline-block; margin-right: 10px;">
                        <option value="">-- All Sources --</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Line">Line</option>
                        <option value="Website">Website</option>
                        <option value="Walk-in">Walk-in</option>
                        <option value="HQ">สำนักงานใหญ่</option>
                        <option value="คนรู้จักแนะนำ">คนรู้จักแนะนำ</option>
                        <option value="อื่นๆ">อื่นๆ</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    @if(isset($summary) && $summary->count() > 0)
    <div class="col-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h5 class="card-title text-primary fw-bold"><i class="fa fa-pie-chart text-info"></i> สรุปข้อมูลแหล่งที่มา (Source Summary)</h5>
            </div>
            <div class="card-body pb-2">
                <div class="row">
                    @foreach($summary as $item)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="p-3 border rounded border-info bg-light text-center shadow-sm transition-hover">
                            <h6 class="text-secondary mb-2">{{ $item->source }}</h6>
                            <h2 class="font-weight-bold mb-0 text-primary">{{ number_format($item->total_count) }} <small class="h6 text-muted">รายการ</small></h2>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="main-card mb-3 card">
        <div class="card-body">
            <table style="width: 100%;" id="Listview" class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                <thead>
                    <tr>
                        <th width="50px">No.</th>
                        <th>Submission Date</th>
                        <th>Receipt Number</th>
                        <th>Parent Name</th>
                        <th>Student(s)</th>
                        <th>Source</th>
                    </tr>
                </thead>
            </table>
        </div>
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
            ajax: {
                url: '{{ route("receipt_school.sourceReport") }}',
                data: function(d) {
                    d.source_filter = $('#source_filter').val();
                }
            },
            serverSide: true,
            processing: true,
            language: {
                loadingRecords: '&nbsp;',
                processing: '<div class="spinner"></div>'
            },
            aaSorting: [[1, "desc"]], // Sort by submission_date initially
            iDisplayLength: 20,
            lengthMenu: [10, 20, 50, 75, 100],
            stateSave: true,
            sPaginationType: "full_numbers",
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'submission_date',
                    name: 'submission_date'
                },
                {
                    data: 'receipt_number',
                    name: 'receipt_number'
                },
                {
                    data: 'name_parent',
                    name: 'name_parent'
                },
                {
                    data: 'pid',
                    name: 'pid',
                    orderable: false
                },
                {
                    data: 'source',
                    name: 'source'
                }
            ]
        });

        $('#source_filter').on('change', function() {
            table.draw();
        });
    });
</script>
@endsection
