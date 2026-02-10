@extends('layouts.main_template')

@section('style')
    <style>
        .holiday-card:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    @if ($message = Session::get('error'))
        <script>
            toastr.error('{{ $message }}');
        </script>
    @endif
    @if ($message = Session::get('success'))
        <script>
            toastr.success('{{ $message }}');
        </script>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-5">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-file-excel me-2"></i> Import วันหยุดจาก Excel
                    </div>
                    <div class="card-body">
                        <form id="importHolidayForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">เลือกไฟล์ Excel (.xlsx)</label>
                                    <input type="file" class="form-control" name="file" id="importFile"
                                        accept=".xlsx,.xls" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-upload me-1"></i> Import
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                รูปแบบไฟล์: คอลัมน์ A=ลำดับ, D=วันที่/เดือน (ภาษาไทย), G=ชื่อวันหยุด
                            </small>
                        </form>
                    </div>
                </div>

                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-calendar-plus me-2"></i> เพิ่มวันหยุดบริษัท (เพิ่มทีละวัน)
                    </div>
                    <div class="card-body">
                        <form id="addHolidayForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">วันที่</label>
                                    <input type="text" class="form-control datepicker" name="date" id="holidayDate"
                                        required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">ชื่อวันหยุด</label>
                                    <input type="text" class="form-control" name="name" id="holidayName"
                                        placeholder="เช่น วันสงกรานต์" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="main-card mb-3 card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-alt me-2"></i> รายการวันหยุดบริษัท</span>
                        <div>
                            <select class="form-select form-select-sm" id="yearSelect" style="width: auto;">
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0" style="padding: 1rem !important">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="25%">วันที่</th>
                                        <th width="15%">วัน</th>
                                        <th width="45%">ชื่อวันหยุด</th>
                                        <th width="15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="holidayList">
                                    @forelse($holidays as $holiday)
                                        <tr class="holiday-card" data-id="{{ $holiday->id }}">
                                            <td>{{ \Carbon\Carbon::parse($holiday->date)->format('d/m/Y') }}</td>
                                            <td>
                                                @php
                                                    $day_names = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
                                                    $day_num = \Carbon\Carbon::parse($holiday->date)->dayOfWeek;
                                                @endphp
                                                <span
                                                    class="badge bg-{{ in_array($day_num, [0, 6]) ? 'danger' : 'secondary' }}">
                                                    {{ $day_names[$day_num] }}
                                                </span>
                                            </td>
                                            <td>{{ $holiday->name }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                    data-id="{{ $holiday->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">ไม่มีวันหยุดในปี
                                                {{ $year }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">รวม {{ count($holidays) }} วัน</small>
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" id="btnRecalculate">
                            <i class="fas fa-sync-alt"></i> คำนวณวันชดเชยใหม่
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="fas fa-gift me-2"></i> สิทธิ์วันลาชดเชยของพนักงาน (ปี {{ $year }})
                    </div>
                    <div class="card-body p-0" id="compensateDaysContainer" style="padding: 1rem !important">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-spinner fa-spin"></i> กำลังโหลด...
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
            LoadCompensateDays();

            $('#yearSelect').on('change', function() {
                window.location.href = '{{ route('company.holidays.index') }}?year=' + $(this).val();
            });

            $('#addHolidayForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('company.holidays.store') }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        } else {
                            toastr.error(response.message || 'เกิดข้อผิดพลาด');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด';
                        toastr.error(msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-plus"></i>');
                    }
                });
            });

            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('ต้องการลบวันหยุดนี้?')) {
                    $.ajax({
                        url: '{{ url('company-holidays') }}/' + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                location.reload();
                            }
                        }
                    });
                }
            });

            $('#btnRecalculate').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('company.holidays.recalculate') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        year: '{{ $year }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            LoadCompensateDays();
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-sync-alt"></i> คำนวณวันชดเชยใหม่');
                    }
                });
            });

            function LoadCompensateDays() {
                $.get('{{ route('company.holidays.compensate') }}?year={{ $year }}', function(html) {
                    $('#compensateDaysContainer').html(html);
                });
            }

            $('#importHolidayForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                var formData = new FormData(this);

                btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> กำลัง Import...');

                $.ajax({
                    url: '{{ route('company.holidays.import') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            if (response.results) {
                                var msg = 'ปี: ' + response.results.year + '\n';
                                msg += 'เพิ่ม: ' + response.results.imported + ' วัน\n';
                                msg += 'ข้าม: ' + response.results.skipped + ' วัน';
                                if (response.results.errors && response.results.errors.length >
                                    0) {
                                    msg += '\nข้อผิดพลาด: ' + response.results.errors.join(
                                    ', ');
                                }
                                alert(msg);
                            }
                            location.reload();
                        } else {
                            toastr.error(response.message || 'เกิดข้อผิดพลาด');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาดในการ Import';
                        toastr.error(msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-upload me-1"></i> Import');
                        form[0].reset();
                    }
                });
            });
        });
    </script>
@endsection
