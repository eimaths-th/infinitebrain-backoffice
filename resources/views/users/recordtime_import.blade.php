@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <style>
        .mapping-row:hover {
            background-color: #f8f9fa;
        }
        .badge-mapped {
            background-color: #28a745;
        }
        .badge-unmapped {
            background-color: #dc3545;
        }
    </style>
@endsection

@section('content')
    @if ($message = Session::get('error'))
        <script>
            toastr.error('{{ $message }}', {
                timeOut: 5000
            });
        </script>
    @endif
    @if ($message = Session::get('success'))
        <script>
            toastr.success('{{ $message }}', {
                timeOut: 5000
            });
        </script>
    @endif

    <div class="row">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fas fa-file-import icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>Import ข้อมูลจากเครื่องสแกนนิ้ว
                        <div class="page-title-subheading">นำเข้าข้อมูลการเข้างานจากไฟล์ Excel ของเครื่องสแกนนิ้ว</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <a href="{{ route('recordtime.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-upload me-2"></i> Upload ไฟล์ Excel
                    </div>
                    <div class="card-body">
                        <form action="{{ route('recordtime.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">เลือกไฟล์ Excel (.xls, .xlsx)</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".xls,.xlsx" required>
                                <div class="form-text">ไฟล์ต้องมีคอลัมน์: ID, วันที่, เข้า, ออก</div>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>สำคัญ:</strong> ต้องกำหนด Fingerprint ID ให้พนักงานก่อนนำเข้าข้อมูล
                            </div>
                            <button type="submit" class="btn btn-primary" id="btnImport">
                                <i class="fas fa-file-import me-1"></i> นำเข้าข้อมูล
                            </button>
                        </form>
                    </div>
                </div>

                @if(isset($import_results))
                <div class="main-card mb-3 card">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-chart-bar me-2"></i> ผลการนำเข้า
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <h3 class="text-success">{{ $import_results['success_count'] }}</h3>
                                <small>สร้างใหม่</small>
                            </div>
                            <div class="col-3">
                                <h3 class="text-info">{{ $import_results['update_count'] ?? 0 }}</h3>
                                <small>อัพเดท</small>
                            </div>
                            <div class="col-3">
                                <h3 class="text-warning">{{ $import_results['skip_count'] }}</h3>
                                <small>ข้าม (ไม่พบ)</small>
                            </div>
                            <div class="col-3">
                                <h3 class="text-danger">{{ $import_results['error_count'] }}</h3>
                                <small>ผิดพลาด</small>
                            </div>
                        </div>
                        @if(isset($import_results['detected_headers']) && count($import_results['detected_headers']) > 0)
                        <hr>
                        <h6>Headers ที่พบในไฟล์:</h6>
                        <p class="text-muted small">{{ implode(', ', $import_results['detected_headers']) }}</p>
                        @if(isset($import_results['header_map']))
                        <p class="text-muted small">
                            Mapped:
                            @foreach($import_results['header_map'] as $key => $idx)
                                <strong>{{ $key }}</strong>=col[{{ $idx }}]
                            @endforeach
                        </p>
                        @endif
                        @endif
                        @if(isset($import_results['employees_from_file']) && count($import_results['employees_from_file']) > 0)
                        <hr>
                        <h6><i class="fas fa-users me-1"></i> พนักงานจากไฟล์ (ใช้สำหรับ Mapping):</h6>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-warning">
                                    <tr>
                                        <th>Fingerprint ID</th>
                                        <th>ชื่อในไฟล์</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($import_results['employees_from_file'] as $fp_id => $name)
                                    <tr>
                                        <td><strong>{{ $fp_id }}</strong></td>
                                        <td>{{ $name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-2 py-2">
                            <small><i class="fas fa-info-circle me-1"></i> กรุณากำหนด Fingerprint ID ให้พนักงานในตารางด้านขวาให้ตรงกับ ID ด้านบน แล้ว Import ใหม่</small>
                        </div>
                        @endif
                        @if(count($import_results['errors']) > 0)
                        <hr>
                        <h6>รายละเอียดข้อผิดพลาด:</h6>
                        <ul class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            @foreach($import_results['errors'] as $error)
                            <li class="list-group-item list-group-item-danger py-1">{{ $error }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-fingerprint me-2"></i> กำหนด Fingerprint ID
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-sm table-hover" id="mappingTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="8%">Code</th>
                                        <th width="15%">ชื่อพนักงาน</th>
                                        <th width="10%">FP ID</th>
                                        <th width="28%">วันหยุด</th>
                                        <th width="28%">วัน WFH</th>
                                        <th width="11%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    @php
                                        $days_off_array = $user->days_off ? explode(',', $user->days_off) : [];
                                        $wfh_days_array = $user->wfh_days ? explode(',', $user->wfh_days) : [];
                                    @endphp
                                    <tr class="mapping-row">
                                        <td>{{ $user->code }}</td>
                                        <td><small>{{ $user->name }}</small></td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm fingerprint-input"
                                                data-user-id="{{ $user->id }}"
                                                value="{{ $user->fingerprint_id }}"
                                                placeholder="ID" style="width:60px">
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach(['0' => 'อา', '1' => 'จ', '2' => 'อ', '3' => 'พ', '4' => 'พฤ', '5' => 'ศ', '6' => 'ส'] as $day_num => $day_name)
                                                <div class="form-check form-check-inline m-0">
                                                    <input class="form-check-input days-off-check" type="checkbox"
                                                        data-user-id="{{ $user->id }}"
                                                        value="{{ $day_num }}"
                                                        {{ in_array($day_num, $days_off_array) ? 'checked' : '' }}>
                                                    <label class="form-check-label small">{{ $day_name }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach(['0' => 'อา', '1' => 'จ', '2' => 'อ', '3' => 'พ', '4' => 'พฤ', '5' => 'ศ', '6' => 'ส'] as $day_num => $day_name)
                                                <div class="form-check form-check-inline m-0">
                                                    <input class="form-check-input wfh-days-check" type="checkbox"
                                                        data-user-id="{{ $user->id }}"
                                                        value="{{ $day_num }}"
                                                        {{ in_array($day_num, $wfh_days_array) ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-primary">{{ $day_name }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success btn-save-fingerprint" data-user-id="{{ $user->id }}">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.btn-save-fingerprint').on('click', function() {
            var btn = $(this);
            var user_id = btn.data('user-id');
            var fingerprint_id = $('input.fingerprint-input[data-user-id="' + user_id + '"]').val();

            var days_off = [];
            $('input.days-off-check[data-user-id="' + user_id + '"]:checked').each(function() {
                days_off.push($(this).val());
            });

            var wfh_days = [];
            $('input.wfh-days-check[data-user-id="' + user_id + '"]:checked').each(function() {
                wfh_days.push($(this).val());
            });

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("recordtime.update.fingerprint") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: user_id,
                    fingerprint_id: fingerprint_id,
                    days_off: days_off.join(','),
                    wfh_days: wfh_days.join(',')
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success('บันทึกสำเร็จ');
                        var badge_cell = btn.closest('td');
                        if(fingerprint_id) {
                            badge_cell.find('.badge').removeClass('bg-secondary').addClass('bg-success').text('OK');
                        } else {
                            badge_cell.find('.badge').remove();
                        }
                    } else {
                        toastr.error(response.message || 'เกิดข้อผิดพลาด');
                    }
                },
                error: function() {
                    toastr.error('เกิดข้อผิดพลาดในการบันทึก');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i>');
                }
            });
        });

        $('.fingerprint-input').on('keypress', function(e) {
            if(e.which === 13) {
                e.preventDefault();
                $(this).closest('tr').find('.btn-save-fingerprint').click();
            }
        });

        $('#importForm').on('submit', function() {
            $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> กำลังนำเข้า...');
        });
    });
</script>
@endsection
