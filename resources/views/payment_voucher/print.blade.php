<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $voucher->voucher_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #525659;
            /* PDF viewer bg feel */
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 15mm;
            margin: 10mm auto;
            border: 1px solid #D3D3D3;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            body {
                background: none;
                margin: 0;
            }

            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .no-print {
                display: none;
            }
        }

        .header-section {
            display: flex;
            margin-bottom: 30px;
        }

        .logo {
            width: auto;
            height: 106px;
            margin-right: 20px;
        }

        .company-info {
            font-size: 11px;
            line-height: 1.5;
        }

        .company-name {
            font-weight: bold;
            font-size: 12px;
        }

        .doc-title {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .doc-title h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .doc-title h2 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 110px 1fr 140px 150px;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .info-label {
            color: #d0d0d0;
            white-space: nowrap;
        }

        .info-value {
            font-weight: 500;
        }

        .underline {
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
            display: inline-block;
            width: 100%;
            min-height: 20px;
        }

        /* Table Styles */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            border-top: 1px solid #81c784;
            /* Greenish border like in sample */
            border-bottom: 1px solid #81c784;
            padding: 8px;
            text-align: center;
            color: #d0d0d0;
            font-weight: normal;
        }

        .items-table td {
            padding: 8px;
            vertical-align: top;
        }

        .items-table td.center {
            text-align: center;
        }

        .items-table td.right {
            text-align: right;
        }

        .text-red {
            color: red;
        }

        .total-row {
            border-top: 1px solid #81c784;
            border-bottom: 1px solid #81c784;
            font-weight: bold;
        }

        .total-label {
            text-align: center;
            padding: 10px;
        }

        .total-amount {
            text-align: right;
            padding: 10px;
            font-size: 16px;
        }

        /* Footer / Signatures */
        .footer-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 30%;
            font-size: 12px;
        }

        .signature-line {
            display: grid;
            grid-template-columns: 75px 1fr;
            margin-bottom: 15px;
            align-items: end;
        }

        .signature-line>div:first-child {
            white-space: nowrap;
        }

        .sig-dots {
            border-bottom: 1px dotted #999;
            height: 20px;
        }

        .bank-info-section {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 10px;
            font-size: 12px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="page">
        <!-- Header -->

        @switch($voucher->branch_code)
            @case('ELPV')
            @case('IF')

            @case('EL')
            @case('IFPV')
                <div class="header-section">
                    <img src="{{ asset('images/infinite-b-icon-3.png') }}" alt="Logo" class="logo" alt="...">
                    <div class="company-info">
                        {{-- <div class="company-name">บริษัท อินฟินิท เบรน จำกัด (สำนักงานใหญ่)</div>
                        <div>555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</div>
                        <div>เลขประจำตัวผู้เสียภาษี : 0125563031038</div>
                        <div>โทร. 098-5098554</div> --}}
                    </div>
                    {{-- <img src="{{ asset('images/infinite-b-icon-3.png') }}" alt="..."> --}}

                    {{-- <div class="company-info">
                        <div class="company-name">บริษัท อินฟินิท เบรน จำกัด (สำนักงานใหญ่)</div>
                            <div>555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</div>
                            <div>เลขประจำตัวผู้เสียภาษี : 0125563031038</div>
                            <div>โทร. 098-5098554</div>
                    </div> --}}
                </div>
                <style>
                    .items-table th {
                        border-top: 1px solid #85ac30;
                        /* Greenish border like in sample */
                        border-bottom: 1px solid #85ac30;
                        padding: 8px;
                        text-align: center;
                        color: #d0d0d0;
                        font-weight: normal;
                    }

                    .total-row {
                        border-top: 1px solid #85ac30;
                        border-bottom: 1px solid #85ac30;
                        font-weight: bold;
                    }
                </style>
            @break

            @case('EiHQ')
            @case('EiBK')

            @case('EiRP')
                <div class="header-section">
                    <img src="{{ asset('images/logo_meta.png') }}" alt="Logo" class="logo" height="106">
                    <div class="company-info">
                        <div class="company-name">META KNOWLEDGE CO., LTD.</div>
                        <div>555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</div>
                        <div>เลขประจำตัวผู้เสียภาษี: 0125565026368</div>
                        <div>โทร 093-358-6333</div>
                    </div>
                </div>
                <style>
                    .items-table th {
                        border-top: 1px solid rgb(255, 115, 0);
                        /* Greenish border like in sample */
                        border-bottom: 1px solid rgb(255, 115, 0);
                        padding: 8px;
                        text-align: center;
                        color: #d0d0d0;
                        font-weight: normal;
                    }

                    .total-row {
                        border-top: 1px solid rgb(255, 115, 0);
                        border-bottom: 1px solid rgb(255, 115, 0);
                        font-weight: bold;
                    }
                </style>
            @break

            @case('MT')
                <div class="header-section">
                    <img src="{{ asset('images/logo_meta_2.png') }}" alt="Logo" class="logo" height="106">
                    <div class="company-info">
                        <div class="company-name">บริษัท เมต้า2 โนวเลจ จำกัด (สำนักงานใหญ่)</div>
                        <div>555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</div>
                        <div>เลขประจำตัวผู้เสียภาษี: 0125567041160</div>
                        <div>โทร 093-358-6333</div>
                    </div>
                </div>
                <style>
                    .items-table th {
                        border-top: 1px solid rgb(255, 115, 0);
                        /* Greenish border like in sample */
                        border-bottom: 1px solid rgb(255, 115, 0);
                        padding: 8px;
                        text-align: center;
                        color: #d0d0d0;
                        font-weight: normal;
                    }

                    .total-row {
                        border-top: 1px solid rgb(255, 115, 0);
                        border-bottom: 1px solid rgb(255, 115, 0);
                        font-weight: bold;
                    }
                </style>
            @break

            @default
        @endswitch



        <div class="doc-title">
            <h1>ใบสำคัญจ่าย</h1>
            <h2>Payment Voucher</h2>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-label">จ่ายให้ / Pay to</div>
            <div class="info-value">{{ $voucher->pay_to }}</div>

            <div class="info-label">เลขที่ / Voucher NO.</div>
            <div class="info-value">{{ $voucher->voucher_no }}</div>

            <div class="info-label">เลขที่บัญชี</div>
            <div class="info-value">
                @if ($voucher->pay_to_account_no)
                    {{ $voucher->pay_to_account_no }}
                    @if ($voucher->pay_to_bank_name)
                        @if (str_starts_with($voucher->pay_to_bank_name, 'ธนาคาร'))
                            {{ $voucher->pay_to_bank_name }}
                        @else
                            ธนาคาร{{ $voucher->pay_to_bank_name }}
                        @endif
                    @endif
                @else
                    -
                @endif
                {{-- <div class="underline" style="margin-top: 5px;"></div> --}}
            </div>

            <div class="info-label">วันที่ / Date</div>
            <div class="info-value">
                {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}
            </div>
            <div style="width: 100px; color: #d0d0d0;">ที่อยู่</div>
            <div style="flex: 1;">
                {{ $voucher->pay_to_address ?? '-' }}
            </div>
            <div class="info-label">เบิกจ่ายประจำสัปดาห์</div>
            <div class="info-value">
                {{ $voucher->payment_cycle ?? '-' }}
            </div>
        </div>

        <div style="display: flex; margin-bottom: 20px;">

        </div>

        <!-- Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">ลำดับที่ / NO.</th>
                    <th style="width: 60%;">รายการ / Description</th>
                    <th style="width: 30%;">จำนวนเงิน / Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voucher->items as $index => $item)
                    <tr class="{{ $item->type === 'deduction' ? 'text-red' : '' }}">
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="right">
                            {{ $item->type === 'deduction' ? '-' : '' }}{{ number_format($item->amount, 2) }}
                        </td>
                    </tr>
                @endforeach

                @if ($voucher->withholding_tax_amount > 0)
                    <tr>
                        <td class="center">{{ $voucher->items->count() + 1 }}</td>
                        <td class="text-red">
                            ภาษีเงินได้หัก ณ ที่จ่าย @if ($voucher->withholding_tax_rate > 0)
                                {{ $voucher->withholding_tax_rate + 0 }} %
                            @endif
                        </td>
                        <td class="right text-red">
                            -{{ number_format($voucher->withholding_tax_amount, 2) }}
                        </td>
                    </tr>
                @endif

                <!-- Spacer rows to keep layout if specific height needed, or just let it flow -->
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="total-label">รวมทั้งสิ้น / Total Amount</td>
                    <td class="total-amount">
                        {{ number_format($voucher->net_amount ?? $voucher->total_amount - $voucher->withholding_tax_amount, 2) }}
                        <span style="font-size: 12px; font-weight: normal; margin-left:10px;">บาท</span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="footer-section">
            <div class="signature-block">
                <div class="signature-line">
                    <div>ผู้อนุมัติ</div>
                    <div class="info-value">
                        @if ($voucher->approved_by_name)
                            {{ $voucher->approved_by_name }}
                        @else
                            <div class="sig-dots"></div>
                        @endif
                    </div>
                </div>

                <div class="signature-line">
                    <div>วันที่</div>
                    <div>
                        {{ $voucher->approved_at ? \Carbon\Carbon::parse($voucher->approved_at)->format('d/m/Y') : '' }}
                    </div>
                </div>
            </div>

            <div class="signature-block">
                <div class="signature-line">
                    <div>ผู้จัดทำ</div>
                    <div class="info-value">
                        @if ($voucher->created_by_name)
                            {{ $voucher->created_by_name }}
                        @else
                            <div class="sig-dots"></div>
                        @endif
                    </div>
                </div>

                <div class="signature-line">
                    <div>เช็คจำนวน</div>
                    <div>
                        {{ number_format($voucher->net_amount ?? $voucher->total_amount - $voucher->withholding_tax_amount, 2) }}
                        บาท</div>
                </div>
            </div>

            <div class="signature-block">
                <div class="signature-line">
                    <div>ผู้จ่ายเงิน</div>
                    <div class="info-value">
                        @if ($voucher->payer_name || $voucher->receiver_name)
                            {{ $voucher->payer_name ?? $voucher->receiver_name }}
                        @else
                            <div class="sig-dots"></div>
                        @endif
                    </div>
                </div>

                <div class="signature-line">
                    <div>วันที่</div>
                    <div>
                        {{ $voucher->paid_date ? \Carbon\Carbon::parse($voucher->paid_date)->format('d/m/Y') : ($voucher->received_date ? \Carbon\Carbon::parse($voucher->received_date)->format('d/m/Y') : '') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attachments -->
    @if ($voucher->allAttachments && $voucher->allAttachments->count() > 0)
        @foreach ($voucher->allAttachments as $attachment)
            @if ($attachment->isImage())
                <div class="page"
                    style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div style="font-weight: bold; margin-bottom: 20px; text-align: left; width: 100%;">
                        เอกสารแนบ: {{ $attachment->file_name }} <br>
                        <span
                            style="font-weight: normal; font-size: 12px; color: #d0d0d0;">{{ $attachment->description }}</span>
                    </div>
                    <img src="{{ $attachment->file_url }}"
                        style="max-width: 100%; max-height: 250mm; object-fit: contain;">
                </div>
            @endif
        @endforeach
    @endif
</body>

</html>
