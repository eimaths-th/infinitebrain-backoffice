<table>
    <thead>
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 14pt; text-align: center;">
                ใบประเมินรายปี (Yearly Evaluation Sheet)
            </th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">
                ประจำปี {{ $year + 543 }}
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
            @foreach ($vouchers->groupBy('voucher_no') as $no => $items)
                <tr>
                    <td style="padding-left: 20px;">- {{ $no }}</td>
                    <td style="text-align: right;">{{ number_format($items->sum('net_amount'), 2) }}</td>
                    <td>{{ $items->first()->pay_to }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="font-weight: bold; text-align: right;">รวมรายจ่ายรายปีทั้งสิ้น</td>
            <td style="font-weight: bold; text-align: right;">{{ number_format($data['total_expense'], 2) }}</td>
            <td>บาท</td>
        </tr>
    </tfoot>
</table>
