<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Voucher - {{ $voucher->voucher_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; font-size: 14px; color: #000; margin: 0; padding: 0; background-color: #525659; }
        .page { width: 210mm; min-height: 297mm; padding: 10mm 15mm; margin: 10mm auto; border: 1px solid #D3D3D3; border-radius: 5px; background: white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); position: relative; box-sizing: border-box; }
        @media print {
            @page { size: auto; margin: 0; }
            body { background: none; margin: 0; }
            .page { margin: 0; border: initial; border-radius: initial; width: initial; min-height: initial; box-shadow: initial; background: initial; page-break-after: always; }
            .no-print { display: none; }
        }
        .header-section { display: flex; margin-bottom: 20px; align-items: flex-start; }
        .logo { width: 200px; height: auto; margin-right: 20px; }
        .doc-title { text-align: center; margin-top: 10px; margin-bottom: 20px; }
        .doc-title h1 { font-size: 22px; font-weight: bold; margin: 0; color: #007bff; }
        .doc-title h2 { font-size: 16px; font-weight: bold; margin: 0; color: #666; }
        .info-grid { display: grid; grid-template-columns: 120px 1fr 120px 1fr; gap: 8px; margin-bottom: 20px; font-size: 13px; }
        .info-label { color: #666; font-weight: bold; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { border-top: 2px solid #007bff; border-bottom: 2px solid #007bff; padding: 8px; text-align: center; background: #f8f9fa; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .total-row { border-top: 2px solid #007bff; border-bottom: 2px solid #007bff; font-weight: bold; background: #e7f3ff; }
        .footer-section { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-block { width: 30%; font-size: 12px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center; }
        .sig-line { border-bottom: 1px dotted #999; margin: 15px 0 5px; height: 20px; }
        .text-red { color: red; }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <div class="header-section">
            <img src="{{ asset('images/infinite-b-icon-3.png') }}" alt="Logo" class="logo">
            <div style="font-size: 11px;">
                <strong>บริษัท อินฟินิท เบรน จำกัด</strong><br>
                555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130
            </div>
        </div>

        <div class="doc-title">
            <h1>ใบสำคัญรับ</h1>
            <h2>Receipt Voucher</h2>
        </div>

        <div class="info-grid">
            <div class="info-label">รับจาก / Received From:</div>
            <div style="grid-column: span 3; border-bottom: 1px solid #ddd;">{{ $voucher->receive_from }}</div>
            
            <div class="info-label">เลขที่ / Voucher NO.:</div>
            <div style="border-bottom: 1px solid #ddd;">{{ $voucher->voucher_no }}</div>
            
            <div class="info-label">วันที่ / Date:</div>
            <div style="border-bottom: 1px solid #ddd;">{{ $voucher->voucher_date->format('d/m/Y') }}</div>
            
            <div class="info-label">ที่อยู่ / Address:</div>
            <div style="grid-column: span 3; border-bottom: 1px solid #ddd;">{{ $voucher->receive_from_address ?: '-' }}</div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">ลำดับ</th>
                    <th>รายการ / Description</th>
                    <th width="20%">จำนวนเงิน / Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voucher->receiptItems as $index => $item)
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td align="right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                
                @if($voucher->deductions->count() > 0)
                    @foreach($voucher->deductions as $index => $item)
                        <tr class="text-red">
                            <td align="center">{{ $voucher->receiptItems->count() + $index + 1 }}</td>
                            <td>(หัก) {{ $item->description }}</td>
                            <td align="right">-{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" align="right">ยอดรับสุทธิ / Net Total Amount</td>
                    <td align="right">{{ number_format($voucher->net_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($voucher->remark)
            <div style="margin-bottom: 20px;">
                <strong>หมายเหตุ:</strong> {{ $voucher->remark }}
            </div>
        @endif

        <div class="footer-section">
            <div class="signature-block">
                <div>ผู้รับเงิน</div>
                <div class="sig-line">{{ $voucher->receiver_name }}</div>
                <div>วันที่: {{ $voucher->received_date ? $voucher->received_date->format('d/m/Y') : '..../..../....' }}</div>
            </div>
            <div class="signature-block">
                <div>ผู้ตรวจสอบ</div>
                <div class="sig-line">{{ $voucher->verified_by_name }}</div>
                <div>วันที่: {{ $voucher->verified_at ? $voucher->verified_at->format('d/m/Y') : '..../..../....' }}</div>
            </div>
            <div class="signature-block">
                <div>ผู้อนุมัติ/ผู้รับเงินทางการ</div>
                <div class="sig-line">{{ $voucher->completed_by_name }}</div>
                <div>วันที่: {{ $voucher->completed_at ? $voucher->completed_at->format('d/m/Y') : '..../..../....' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
