@extends('layouts.main_template')

@section('style')
    <style>
        .preview-table th {
            white-space: nowrap;
            font-size: 12px;
        }

        .preview-table td {
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .not-found {
            background-color: #fff3cd;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>ตรวจสอบข้อมูลก่อนนำเข้า
                    </h5>
                    <p class="text-muted mb-0 mt-1">งวด: {{ $period->period_name }}</p>
                </div>
                <a href="{{ route('payroll.import', $period->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-upload me-1"></i> เลือกไฟล์ใหม่
                </a>
            </div>
            <div class="card-body">
                @if (count($preview_data) == 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ไม่พบข้อมูลในไฟล์ Excel
                    </div>
                    <a href="{{ route('payroll.import', $period->id) }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> กลับ
                    </a>
                @else
                    <form action="{{ route('payroll.import.process', $period->id) }}" method="POST">
                        @csrf

                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            พบข้อมูล {{ count($preview_data) }} รายการ
                            - เลือกรายการที่ต้องการนำเข้า แล้วกดปุ่ม "นำเข้าข้อมูล"
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-hover preview-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">
                                            <input type="checkbox" id="select-all" checked>
                                        </th>
                                        <th>รหัส</th>
                                        <th>ชื่อ</th>
                                        <th>ตำแหน่ง</th>
                                        <th class="text-right">เงินเดือน</th>
                                        <th class="text-right">OT</th>
                                        <th class="text-right">ค่าเดินทาง</th>
                                        <th class="text-right">อื่นๆ</th>
                                        <th class="text-right">รวมได้รับ</th>
                                        <th class="text-right">ปกส.</th>
                                        <th class="text-right">ภาษี</th>
                                        <th class="text-right">หักอื่นๆ</th>
                                        <th class="text-right">รวมหัก</th>
                                        <th class="text-right">สุทธิ</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview_data as $index => $row)
                                        @php
                                            $user = \App\Models\User::where('code', $row['code'])->first();
                                            $has_existing =
                                                $user &&
                                                \App\Models\Payroll::where('payroll_period_id', $period->id)
                                                    ->where('user_id', $user->id)
                                                    ->exists();
                                        @endphp
                                        <tr class="{{ $user ? '' : 'not-found' }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="selected[{{ $row['code'] }}]" value="1"
                                                    {{ $user ? 'checked' : '' }} class="row-checkbox">
                                            </td>
                                            <td>{{ $row['code'] }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['position'] }}</td>
                                            <td class="text-right">{{ number_format($row['salary'], 2) }}</td>
                                            <td class="text-right">{{ number_format($row['ot'], 2) }}</td>
                                            <td class="text-right">{{ number_format($row['travel'], 2) }}</td>
                                            <td class="text-right">{{ number_format($row['other_income'] ?? 0, 2) }}</td>
                                            <td class="text-right text-success">
                                                {{ number_format($row['total_income'], 2) }}</td>
                                            <td class="text-right">{{ number_format($row['social_security'], 2) }}</td>
                                            <td class="text-right">{{ number_format($row['tax'], 2) }}</td>
                                            <td class="text-right">{{ number_format($row['other_deduction'] ?? 0, 2) }}
                                            </td>
                                            <td class="text-right text-danger">
                                                {{ number_format($row['total_deduction'], 2) }}</td>
                                            <td class="text-right fw-bold">{{ number_format($row['net_pay'], 2) }}</td>
                                            <td>
                                                @if (!$user)
                                                    <span class="badge bg-warning text-dark">ไม่พบในระบบ</span>
                                                @elseif($has_existing)
                                                    <span class="badge bg-info">จะอัปเดต</span>
                                                @else
                                                    <span class="badge bg-success">พร้อมนำเข้า</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <!-- Hidden inputs for form submission -->
                                        <input type="hidden" name="import_data[{{ $index }}][code]"
                                            value="{{ $row['code'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][name]"
                                            value="{{ $row['name'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][position]"
                                            value="{{ $row['position'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][salary]"
                                            value="{{ $row['salary'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][ot]"
                                            value="{{ $row['ot'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][travel]"
                                            value="{{ $row['travel'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][other_income]"
                                            value="{{ $row['other_income'] ?? 0 }}">
                                        <input type="hidden" name="import_data[{{ $index }}][total_income]"
                                            value="{{ $row['total_income'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][social_security]"
                                            value="{{ $row['social_security'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][tax]"
                                            value="{{ $row['tax'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][other_deduction]"
                                            value="{{ $row['other_deduction'] ?? 0 }}">
                                        <input type="hidden" name="import_data[{{ $index }}][total_deduction]"
                                            value="{{ $row['total_deduction'] }}">
                                        <input type="hidden" name="import_data[{{ $index }}][net_pay]"
                                            value="{{ $row['net_pay'] }}">
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> นำเข้าข้อมูลที่เลือก
                            </button>
                            <a href="{{ route('payroll.import', $period->id) }}" class="btn btn-secondary">
                                <i class="fas fa-upload me-1"></i> เลือกไฟล์ใหม่
                            </a>
                            <a href="{{ route('payroll.show', $period->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> ยกเลิก
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.getElementById('select-all')?.addEventListener('change', function() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    </script>
@endsection
