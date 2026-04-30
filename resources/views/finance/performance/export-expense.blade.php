<table>
    <thead>
        <tr>
            <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: center;">
                รายงานรายละเอียดรายจ่าย {{ $isEvaluation ? '(แบบประเมิน)' : '' }}
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center;">
                ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
            </th>
        </tr>
        @php $section_no = 1; @endphp
        <tr>
            <th colspan="5" style="font-weight: bold; background-color: #f2f2f2;">{{ $section_no++ }}. Payment Vouchers (ใบสำคัญจ่าย)</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center;">วันที่</th>
            <th style="font-weight: bold; text-align: center;">เลขที่เอกสาร</th>
            <th style="font-weight: bold; text-align: center;">รายการ / ผู้รับเงิน</th>
            <th style="font-weight: bold; text-align: center;">หมวดหมู่</th>
            <th style="font-weight: bold; text-align: center;">จำนวนเงิน</th>
        </tr>
    </thead>
    <tbody>
        @php $pv_total = 0; @endphp
        @foreach ($data['details']['pv_items']->groupBy('branch_code') as $branch => $vouchers)
            <tr>
                <td colspan="5" style="font-weight: bold; background-color: #e9ecef;">สาขา: {{ $branch }}</td>
            </tr>
            @foreach ($vouchers as $voucher)
                @foreach ($voucher->expenseItems as $item)
                    <tr>
                        <td style="text-align: center;">{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                        <td style="text-align: center;">{{ $voucher->voucher_no }}</td>
                        <td>{{ $item->description }} ({{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }})</td>
                        <td style="text-align: center;">{{ \App\Models\PaymentVoucher::getAllCategoryLabels()[$voucher->category] ?? 'อื่นๆ' }}</td>
                        <td style="text-align: right;">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @php $pv_total += $item->amount; @endphp
                @endforeach
            @endforeach
        @endforeach
        <tr>
            <td colspan="4" style="font-weight: bold; text-align: right;">รวม Payment Vouchers</td>
            <td style="font-weight: bold; text-align: right;">{{ number_format($pv_total, 2) }}</td>
        </tr>

        @if (empty($isEvaluation) && $data['details']['payroll_items']->count() > 0)
            <tr>
                <td colspan="5"></td>
            </tr>
            <tr>
                <th colspan="5" style="font-weight: bold; background-color: #f2f2f2;">{{ $section_no++ }}. Payroll (เงินเดือนพนักงาน)</th>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold; text-align: center;">แผนก</th>
                <th colspan="2" style="font-weight: bold; text-align: center;">ชื่อ-นามสกุล</th>
                <th style="font-weight: bold; text-align: center;">ยอดสุทธิ</th>
            </tr>
            @php $payroll_total = 0; @endphp
            @foreach ($data['details']['payroll_items']->groupBy(function($item) { return $item->user->department ?? 'ไม่ระบุแผนก'; }) as $dept => $items)
                @foreach ($items as $item)
                    <tr>
                        <td colspan="2" style="text-align: center;">{{ $dept }}</td>
                        <td colspan="2">{{ $item->user->name ?? $item->user_id }}</td>
                        <td style="text-align: right;">{{ number_format($item->net_pay, 2) }}</td>
                    </tr>
                    @php $payroll_total += $item->net_pay; @endphp
                @endforeach
            @endforeach
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: right;">รวม Payroll</td>
                <td style="font-weight: bold; text-align: right;">{{ number_format($payroll_total, 2) }}</td>
            </tr>
        @endif

        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <th colspan="5" style="font-weight: bold; background-color: #ffeeba; color: #856404;">{{ $section_no++ }}. สรุปรายการหักประจำเดือน (Deductions)</th>
        </tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #f39c12; color: white; text-align: center;">ประเภทรายการหัก</th>
            <th style="font-weight: bold; background-color: #f39c12; color: white; text-align: center;">จำนวนเงิน</th>
        </tr>
        <tr>
            <td colspan="4">รายการหักจากใบสำคัญจ่าย</td>
            <td style="text-align: right;">{{ number_format($data['pv_deductions'], 2) }}</td>
        </tr>
        @if (empty($isEvaluation))
            <tr>
                <td colspan="4">รายการหักจากเงินเดือน</td>
                <td style="text-align: right;">{{ number_format($data['payroll_deductions'], 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="4" style="font-weight: bold; text-align: right;">รวมรายการหักทั้งหมด</td>
            <td style="font-weight: bold; text-align: right;">{{ number_format($data['pv_deductions'] + (empty($isEvaluation) ? $data['payroll_deductions'] : 0), 2) }}</td>
        </tr>

        <tr>
            <td colspan="5"></td>
        </tr>
        <tr style="background-color: #fde8e8;">
            <td colspan="4" style="font-weight: bold; text-align: right; font-size: 12pt;">ยอดรวมรายจ่ายสุทธิทั้งสิ้น</td>
            <td style="font-weight: bold; text-align: right; font-size: 12pt;">{{ number_format($data['total_expense'], 2) }}</td>
        </tr>
    </tbody>
</table>
