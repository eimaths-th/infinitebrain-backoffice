<table>
    <tr>
        <td colspan="4" style="text-align: center; font-size: 16px; font-weight: bold;">
            ใบสำคัญจ่าย (Payment Voucher)
        </td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td style="font-weight: bold;">เลขที่:</td>
        <td>{{ $voucher->voucher_no }}</td>
        <td style="font-weight: bold;">วันที่:</td>
        <td>{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">จ่ายให้:</td>
        <td colspan="3">{{ $voucher->pay_to }}</td>
    </tr>
    @if($voucher->pay_to_address)
    <tr>
        <td style="font-weight: bold;">ที่อยู่:</td>
        <td colspan="3">{{ $voucher->pay_to_address }}</td>
    </tr>
    @endif
    <tr><td></td></tr>
    <tr>
        <td style="font-weight: bold; border: 1px solid black; text-align: center;">ลำดับ</td>
        <td style="font-weight: bold; border: 1px solid black; text-align: center;">รายละเอียด</td>
        <td style="font-weight: bold; border: 1px solid black; text-align: center;">จำนวนเงิน</td>
        <td style="font-weight: bold; border: 1px solid black; text-align: center;">หมายเหตุ</td>
    </tr>
    @foreach($voucher->items as $item)
    <tr>
        <td style="border: 1px solid black; text-align: center;">{{ $item->item_no }}</td>
        <td style="border: 1px solid black;">{{ $item->description }}</td>
        <td style="border: 1px solid black; text-align: right;">{{ number_format($item->amount, 2) }}</td>
        <td style="border: 1px solid black;"></td>
    </tr>
    @endforeach
    <tr>
        <td colspan="2" style="border: 1px solid black; text-align: right; font-weight: bold;">รวมทั้งสิ้น</td>
        <td style="border: 1px solid black; text-align: right; font-weight: bold;">{{ number_format($voucher->total_amount, 2) }}</td>
        <td style="border: 1px solid black;"></td>
    </tr>
    <tr><td></td></tr>
    @if($voucher->remark)
    <tr>
        <td style="font-weight: bold;">หมายเหตุ:</td>
        <td colspan="3">{{ $voucher->remark }}</td>
    </tr>
    @endif
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr>
        <td style="font-weight: bold;">สถานะ:</td>
        <td>{{ $voucher->status_label }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td colspan="2" style="text-align: center;">
            <div>ผู้จัดทำ</div>
            <div style="margin-top: 30px;">{{ $voucher->created_by_name }}</div>
        </td>
        <td colspan="2" style="text-align: center;">
            <div>ผู้อนุมัติ</div>
            <div style="margin-top: 30px;">{{ $voucher->approved_by_name ?? '________________' }}</div>
        </td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td colspan="2" style="text-align: center;">
            <div>ผู้ตรวจสอบ</div>
            <div style="margin-top: 30px;">{{ $voucher->reviewed_by_name ?? '________________' }}</div>
        </td>
        <td colspan="2" style="text-align: center;">
            <div>ผู้รับเงิน</div>
            <div style="margin-top: 30px;">{{ $voucher->receiver_name ?? '________________' }}</div>
        </td>
    </tr>
</table>
