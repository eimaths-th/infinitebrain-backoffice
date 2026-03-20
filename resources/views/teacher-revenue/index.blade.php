@extends('layouts.main_template')

@section('content')
<div class="app-main__inner">
    {{-- หัวข้อหน้า --}}
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-users icon-gradient bg-midnight-bloom"></i>
                </div>
                <div>รายได้ครูจากการสอน (Teacher Revenue)
                    <div class="page-title-subheading">
                        สรุปรายได้ที่ครูแต่ละคนสร้าง คำนวณจาก ชั่วโมงสอน × (ราคาคอร์ส / {{ App\Http\Controllers\TeacherRevenueController::HOURS_PER_COURSE }} ชม.)
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ตัวกรอง ปี/เดือน --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="main-card card">
                <div class="card-body">
                    <form action="{{ route('teacher-revenue.index') }}" method="GET" class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">ปี</label>
                            <select name="year" class="form-control">
                                @for ($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">เดือน</label>
                            <select name="month" class="form-control">
                                <option value="">ทั้งปี</option>
                                @foreach(\App\Models\PayrollPeriod::$thaiMonths as $m => $name)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> ค้นหา
                            </button>
                            <a href="{{ route('teacher-revenue.index') }}" class="btn btn-secondary ms-1">รีเซ็ต</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- สรุป Cards --}}
    @php
        $total_all_revenue = collect($teacher_revenue)->sum('total_revenue');
        $total_all_hours = collect($teacher_revenue)->sum('total_hours');
        $total_all_sessions = collect($teacher_revenue)->sum('session_count');
    @endphp
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card widget-content bg-midnight-bloom">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading">รายได้รวมทั้งหมด</div>
                        <div class="widget-subheading">ครู {{ count($teacher_revenue) }} คน</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-white"><span>{{ number_format($total_all_revenue, 0) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card widget-content bg-happy-green">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading">ชั่วโมงสอนรวม</div>
                        <div class="widget-subheading">{{ $total_all_sessions }} ครั้ง</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-white"><span>{{ number_format($total_all_hours, 1) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card widget-content bg-premium-dark">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading">ไม่พบใบเสร็จ</div>
                        <div class="widget-subheading">ไม่สามารถคำนวณรายได้ได้</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-warning"><span>{{ count($unmatched_records) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ตารางสรุปรายได้ครูแต่ละคน --}}
    <div class="row">
        <div class="col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header-tab card-header">
                    <div class="card-header-title font-size-lg text-capitalize font-weight-normal">
                        <i class="header-icon pe-7s-graph1 icon-gradient bg-happy-itmeo"></i>
                        สรุปรายได้ครูแต่ละคน
                        @if($month)
                            — {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} {{ $year + 543 }}
                        @else
                            — ปี {{ $year + 543 }}
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th>ชื่อครู</th>
                                <th class="text-center">จำนวนครั้ง</th>
                                <th class="text-end">ชั่วโมงสอน</th>
                                <th class="text-end">รายได้ (บาท)</th>
                                <th class="text-end">เฉลี่ย/ชม.</th>
                                <th class="text-center">รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teacher_revenue as $idx => $t)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="font-weight-bold">{{ $t['teacher_name'] }}</td>
                                    <td class="text-center">{{ $t['session_count'] }}</td>
                                    <td class="text-end">{{ number_format($t['total_hours'], 1) }} ชม.</td>
                                    <td class="text-end font-weight-bold text-success">{{ number_format($t['total_revenue'], 2) }}</td>
                                    <td class="text-end">
                                        @if($t['total_hours'] > 0)
                                            {{ number_format($t['total_revenue'] / $t['total_hours'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#monthly_{{ $t['teacher_id'] }}"
                                                aria-expanded="false">
                                            <i class="fa fa-chevron-down"></i> รายเดือน
                                        </button>
                                    </td>
                                </tr>
                                {{-- แถวรายละเอียดรายเดือน (ซ่อน/แสดง) --}}
                                <tr class="collapse" id="monthly_{{ $t['teacher_id'] }}">
                                    <td colspan="7" class="p-0">
                                        <table class="table table-sm table-bordered mb-0" style="background: #f8f9fa;">
                                            <thead style="background: #e9ecef;">
                                                <tr>
                                                    <th class="text-center" style="width: 30%;">เดือน</th>
                                                    <th class="text-center" style="width: 20%;">จำนวนครั้ง</th>
                                                    <th class="text-end" style="width: 25%;">ชั่วโมง</th>
                                                    <th class="text-end" style="width: 25%;">รายได้ (บาท)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($t['monthly'] as $m_key => $m_data)
                                                    @php
                                                        $m_parts = explode('-', $m_key);
                                                        $m_label = \App\Models\PayrollPeriod::$thaiMonths[(int)$m_parts[1]] . ' ' . ($m_parts[0] + 543);
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $m_label }}</td>
                                                        <td class="text-center">{{ $m_data['sessions'] }}</td>
                                                        <td class="text-end">{{ number_format($m_data['hours'], 1) }}</td>
                                                        <td class="text-end text-success font-weight-bold">{{ number_format($m_data['revenue'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="pe-7s-info fa-2x"></i><br>
                                        ไม่พบข้อมูลการสอนที่มีครู (user_id) ในช่วงเวลาที่เลือก
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($teacher_revenue) > 0)
                            <tfoot class="bg-light">
                                <tr class="font-weight-bold">
                                    <td colspan="2" class="text-end">รวมทั้งหมด</td>
                                    <td class="text-center">{{ $total_all_sessions }}</td>
                                    <td class="text-end">{{ number_format($total_all_hours, 1) }} ชม.</td>
                                    <td class="text-end text-success">{{ number_format($total_all_revenue, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- บล็อค: นักเรียนที่ไม่พบใบเสร็จ --}}
    @if(count($unmatched_records) > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card border-warning">
                    <div class="card-header-tab card-header bg-warning text-white">
                        <div class="card-header-title font-size-lg text-capitalize font-weight-normal text-white">
                            <i class="header-icon fa fa-exclamation-triangle"></i>
                            รายการที่ไม่พบใบเสร็จ ({{ count($unmatched_records) }} รายการ)
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="alert alert-warning m-3 mb-0">
                            <strong>หมายเหตุ:</strong> รายการเหล่านี้ไม่สามารถคำนวณรายได้ได้เนื่องจากไม่พบข้อมูลใบเสร็จ (school_price) ที่ตรงกับ นักเรียน + วิชา
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%;">#</th>
                                        <th>ครูผู้สอน</th>
                                        <th>นักเรียน</th>
                                        <th>วิชา</th>
                                        <th class="text-center">วันที่</th>
                                        <th class="text-center">เวลา</th>
                                        <th class="text-end">ชั่วโมง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unmatched_records as $idx => $r)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td>{{ $r->teacher_name }}</td>
                                            <td>{{ $r->student_name }} {{ $r->student_nickname ? '(' . $r->student_nickname . ')' : '' }}</td>
                                            <td>{{ $r->subject_name }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($r->datetime)->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ $r->stime }} - {{ $r->etime }}</td>
                                            <td class="text-end">{{ number_format($r->teaching_hours, 1) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
