<div class="table-responsive">
    <table class="table table-hover payroll-table mb-0">
        <thead>
            <tr>
                <th style="width: 50px">#</th>
                <th>รหัส</th>
                <th>ชื่อ-นามสกุล</th>
                <th>แผนก</th>
                <th>ตำแหน่ง</th>
                <th class="text-end">เงินเดือน</th>
                <th class="text-end">รายได้รวม</th>
                <th class="text-end">หักรวม</th>
                <th class="text-end">สุทธิ</th>
                <th class="text-center" style="width: 150px">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payrolls as $index => $payroll)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $payroll->employee_code }}</td>
                    <td>
                        <strong>{{ $payroll->employee_name }}</strong>
                        @if (!$payroll->calc_social_security && $payroll->deductionItems->where('deduction_type', 'social_security')->isEmpty())
                            <span class="badge bg-secondary ms-1" title="ไม่หัก ปกส.">ไม่หัก ปกส.</span>
                        @endif
                    </td>
                    <td>{{ $payroll->user?->department ?: '-' }}</td>
                    <td>{{ $payroll->position }}</td>
                    <td class="text-end">{{ number_format($payroll->base_salary, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($payroll->total_income, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($payroll->total_deduction, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($payroll->net_pay, 2) }}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            @if ($period->CanEdit())
                                <a href="{{ route('payroll.edit-payroll', [$period->id, $payroll->id]) }}"
                                    class="btn btn-outline-primary action-btn" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            <a href="{{ route('payroll.payslip', [$period->id, $payroll->id]) }}"
                                class="btn btn-outline-info action-btn" title="ใบเงินเดือน" target="_blank">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            @if ($period->CanEdit())
                                <form action="{{ route('payroll.delete-payroll', [$period->id, $payroll->id]) }}"
                                    method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger action-btn" title="ลบ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light">
                <th colspan="5" class="text-end">รวม</th>
                <th class="text-end">{{ number_format($payrolls->sum('base_salary'), 2) }}</th>
                <th class="text-end text-success">{{ number_format($payrolls->sum('total_income'), 2) }}</th>
                <th class="text-end text-danger">{{ number_format($payrolls->sum('total_deduction'), 2) }}</th>
                <th class="text-end fw-bold">{{ number_format($payrolls->sum('net_pay'), 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
