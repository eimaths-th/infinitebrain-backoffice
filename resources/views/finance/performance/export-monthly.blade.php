<table>
    <thead>
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 14pt; text-align: center;">
                ใบประเมิน (Evaluation Sheet) 
            </th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">
                ประจำเดือน {{ \App\Models\PayrollPeriod::$thaiMonths[$month] }} ปี {{ $year + 543 }}
            </th>
        </tr>
        <tr>
            <th>รายการ</th>
            <th style="text-align: right;">จำนวนเงิน</th>
            <th>หมายเหตุ</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['details']['pv_items']->groupBy('category') as $category => $vouchers)
            @php
                $categoryName = \App\Models\PaymentVoucher::getAllCategoryLabels()[$category] ?? 'อื่นๆ';
                $categoryTotal = $vouchers->sum('net_amount');
            @endphp
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2;">หมวดหมู่: {{ $categoryName }}</td>
                <td style="font-weight: bold; background-color: #f2f2f2; text-align: right;">{{ number_format($categoryTotal, 2) }}</td>
                <td style="background-color: #f2f2f2;"></td>
            </tr>
            @foreach ($vouchers as $voucher)
                @foreach ($voucher->expenseItems as $item)
                    <tr>
                        <td style="padding-left: 20px;">- {{ $item->description }} ({{ $voucher->voucher_no }})</td>
                        <td style="text-align: right;">{{ number_format($item->amount, 2) }}</td>
                        <td>{{ $voucher->payee ? $voucher->payee->name : $voucher->pay_to }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="font-weight: bold; text-align: right;">รวมรายจ่ายทั้งสิ้น</td>
            <td style="font-weight: bold; text-align: right;">{{ number_format($data['total_expense'], 2) }}</td>
            <td>บาท</td>
        </tr>
    </tfoot>
</table>
