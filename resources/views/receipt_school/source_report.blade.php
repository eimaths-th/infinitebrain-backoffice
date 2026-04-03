@extends('layouts.main_template')

@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <style>
        /* ปรับสไตล์ปุ่ม Print ให้ดูดี (Premium Look) */
        .btn-print-report {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            border: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-print-report:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.4);
            color: white !important;
        }
        
        .transition-hover {
            transition: all 0.2s ease-in-out;
        }
        
        .transition-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
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
                    <label class="mr-1">ปี (Year):</label>
                    <select id="year_filter" class="form-control" style="width: 110px; display: inline-block; margin-right: 10px;">
                        <option value="">-- ทั้งหมด --</option>
                        @for ($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ request('year_filter') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="d-inline-block">
                    <label class="mr-1">เดือน (Month):</label>
                    <select id="month_filter" class="form-control" style="width: 140px; display: inline-block; margin-right: 10px;">
                        <option value="">-- ทั้งหมด --</option>
                        @php
                            $months_th = [
                                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                            ];
                        @endphp
                        @foreach($months_th as $num => $month_name)
                            <option value="{{ $num }}" {{ request('month_filter') == $num ? 'selected' : '' }}>{{ $month_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-inline-block">
                    <label class="mr-1">แหล่งที่มา (Source):</label>
                    <select id="source_filter" class="form-control" style="width: 180px; display: inline-block; margin-right: 10px;">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="Facebook" {{ request('source_filter') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="Line" {{ request('source_filter') == 'Line' ? 'selected' : '' }}>Line</option>
                        <option value="Website" {{ request('source_filter') == 'Website' ? 'selected' : '' }}>Website</option>
                        <option value="Walk-in" {{ request('source_filter') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                        <option value="HQ" {{ request('source_filter') == 'HQ' ? 'selected' : '' }}>สำนักงานใหญ่</option>
                        <option value="คนรู้จักแนะนำ" {{ request('source_filter') == 'คนรู้จักแนะนำ' ? 'selected' : '' }}>คนรู้จักแนะนำ</option>
                        <option value="อื่นๆ" {{ request('source_filter') == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
                    </select>
                </div>
                <!-- ปุ่มพิมพ์รายงานใหม่ที่เปิด Tab ใหม่ -->
                <button type="button" class="btn btn-print-report shadow-sm" onclick="HandlePrintAsync()">
                    <i class="fa fa-print me-1"></i> พิมพ์รายงาน (Print)
                </button>
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
                        <th>Remark</th>
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
                    d.year_filter = $('#year_filter').val();
                    d.month_filter = $('#month_filter').val();
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
                },
                {
                    data: 'remark',
                    name: 'remark'
                }
            ]
        });

        $('#source_filter, #year_filter, #month_filter').on('change', function() {
            // อัปเดต URL และโหลดหน้าใหม่เพื่อให้ Summary Boxes ตรงตามฟิลเตอร์
            const source_val = $('#source_filter').val();
            const year_val = $('#year_filter').val();
            const month_val = $('#month_filter').val();
            
            const target_url = new URL(window.location.href);
            target_url.searchParams.set('source_filter', source_val);
            target_url.searchParams.set('year_filter', year_val);
            target_url.searchParams.set('month_filter', month_val);
            
            window.location.href = target_url.toString();
        });
    });

    /**
     * ฟังก์ชันสำหรับการพิมพ์รายงาน
     * เปิดหน้าจอพิมพ์รายงานแบบมืออาชีพใน Tab ใหม่
     */
    function HandlePrintAsync() {
        const SOURCE_FILTER = $('#source_filter').val() || '';
        const YEAR_FILTER = $('#year_filter').val() || '';
        const MONTH_FILTER = $('#month_filter').val() || '';
        
        let printUrl = "{{ route('receipt_school.sourceReportPrint') }}?";
        printUrl += "source_filter=" + encodeURIComponent(SOURCE_FILTER);
        printUrl += "&year_filter=" + encodeURIComponent(YEAR_FILTER);
        printUrl += "&month_filter=" + encodeURIComponent(MONTH_FILTER);
        
        // เปิดหน้าต่างพิมพ์ใน Tab ใหม่
        window.open(printUrl, '_blank');
    }
</script>
@endsection
