<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            background: #e8eaf0;
            color: #1a1a2e;
        }

        /* ── Toolbar (Screen only) ── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            background: #1a1a2e;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .toolbar h1 { font-size: 1rem; flex: 1; font-weight: 500; }
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn-toolbar:hover { opacity: 0.85; }
        .btn-print { background: #4CAF50; color: #fff; }
        .btn-back  { background: #546e7a; color: #fff; }

        /* ── A4 Page ── */
        .page-wrapper {
            display: flex;
            justify-content: center;
            padding: 32px 16px 48px;
            min-height: calc(100vh - 56px);
        }

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.18);
            border-radius: 4px;
            padding: 20mm 18mm 16mm;
            position: relative;
        }

        /* ── Document Header ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
        }
        .doc-header-left .company-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
        }
        .doc-header-left .company-sub {
            font-size: 0.8rem;
            color: #666;
            margin-top: 2px;
        }
        .doc-header-right {
            text-align: right;
        }
        .doc-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.2;
        }
        .doc-subtitle {
            font-size: 0.85rem;
            color: #666;
            margin-top: 4px;
        }

        /* ── Info Box ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            background: #f8f9fc;
            border: 1px solid #e3e6ef;
            border-left: 4px solid {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
            border-radius: 4px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }
        .info-item { display: flex; gap: 6px; align-items: baseline; }
        .info-label { color: #888; font-size: 0.8rem; white-space: nowrap; }
        .info-value { font-weight: 600; font-size: 0.9rem; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        thead th {
            background: {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
            color: #fff;
            padding: 10px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 0.82rem;
            letter-spacing: 0.02em;
        }
        thead th:first-child { border-radius: 4px 0 0 0; }
        thead th:last-child  { border-radius: 0 4px 0 0; }
        tbody tr { border-bottom: 1px solid #edf0f7; }
        tbody tr:nth-child(even) { background: #f8f9fc; }
        tbody tr:hover { background: #eef2ff; }
        tbody td { padding: 9px 10px; vertical-align: middle; }

        .voucher-link {
            color: {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
            text-decoration: none;
            font-weight: 600;
        }
        .voucher-link:hover { text-decoration: underline; }

        .amount-cell { font-weight: 600; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #888; font-size: 0.8rem; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-success  { background: #d4edda; color: #155724; }
        .badge-warning  { background: #fff3cd; color: #856404; }
        .badge-info     { background: #d1ecf1; color: #0c5460; }
        .badge-secondary{ background: #e2e3e5; color: #383d41; }
        .badge-danger   { background: #f8d7da; color: #721c24; }

        /* ── Summary Footer ── */
        .summary-section {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }
        .summary-box {
            background: #f8f9fc;
            border: 1px solid #e3e6ef;
            border-top: 3px solid {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
            border-radius: 4px;
            padding: 12px 20px;
            min-width: 260px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px dashed #e3e6ef;
            font-size: 0.88rem;
        }
        .summary-row:last-child { border-bottom: none; }
        .summary-row.total {
            font-size: 1rem;
            font-weight: 700;
            color: {{ $type === 'rv' ? '#059669' : '#3f6ad8' }};
            padding-top: 8px;
        }
        .summary-row .label { color: #555; }
        .summary-row .value { font-weight: 600; }

        /* ── Doc Footer ── */
        .doc-footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px dashed #ccc;
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #aaa;
        }

        /* ── Print ── */
        @media print {
            body { background: white; font-size: 13px; }
            .toolbar { display: none !important; }
            .page-wrapper { padding: 0; background: white; }
            .a4-page {
                width: 210mm;
                min-height: 297mm;
                box-shadow: none;
                border-radius: 0;
                padding: 15mm 15mm 12mm;
            }
            tbody tr:hover { background: inherit; }
            .voucher-link { color: inherit; }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    {{-- ── Toolbar (Screen) ── --}}
    <div class="toolbar">
        <h1>
            {{ $type === 'rv' ? '📥 ใบสำคัญรับ' : '📤 ใบสำคัญจ่าย' }} — {{ $branch_name }} ({{ $branch_code }})
        </h1>
        <a href="{{ route('finance-dashboard.index', ['year' => $year, 'month' => $month]) }}" class="btn-toolbar btn-back">
            ← กลับ Dashboard
        </a>
        <button onclick="window.print()" class="btn-toolbar btn-print">
            🖨️ พิมพ์ / บันทึก PDF
        </button>
    </div>

    {{-- ── A4 Page ── --}}
    <div class="page-wrapper">
        <div class="a4-page">

            {{-- Header --}}
            <div class="doc-header">
                <div class="doc-header-left">
                    <div class="company-name">InfiniteBrain</div>
                    <div class="company-sub">ระบบการเงินและบัญชี</div>
                </div>
                <div class="doc-header-right">
                    <div class="doc-title">
                        @if($type === 'rv') รายงานใบสำคัญรับ @else รายงานใบสำคัญจ่าย @endif
                    </div>
                    <div class="doc-subtitle">
                        Branch Detail Report — สาขา {{ $branch_code }}
                    </div>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">สาขา :</span>
                    <span class="info-value">{{ $branch_name }} ({{ $branch_code }})</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ประเภท :</span>
                    <span class="info-value">{{ $type === 'rv' ? 'ใบสำคัญรับ (RV)' : 'ใบสำคัญจ่าย (PV)' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ช่วงเวลา :</span>
                    <span class="info-value">
                        @if($period === 'monthly')
                            รายเดือน — {{ \App\Models\PayrollPeriod::$thaiMonths[$month] ?? $month }} {{ $year + 543 }}
                        @elseif($period === 'yearly')
                            รายปี — ปี {{ $year + 543 }}
                        @else
                            ข้อมูลทั้งหมด
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">วันที่พิมพ์ :</span>
                    <span class="info-value">{{ now()->locale('th')->translatedFormat('d F Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">จำนวนรายการ :</span>
                    <span class="info-value">{{ count($vouchers) }} รายการ</span>
                </div>
                <div class="info-item">
                    <span class="info-label">สถานะ :</span>
                    <span class="info-value">เสร็จสิ้น (Completed) เท่านั้น</span>
                </div>
            </div>

            {{-- Table --}}
            @php
                $grand_total = 0;
                $grand_tax = 0;
                $grand_net = 0;
            @endphp
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width:36px;">#</th>
                        <th style="width:110px;">เลขที่ใบสำคัญ</th>
                        <th style="width:86px;">วันที่</th>
                        <th>{{ $type === 'rv' ? 'รับจาก' : 'จ่ายให้' }}</th>
                        <th>รายละเอียด</th>
                        <th class="text-right" style="width:110px;">จำนวนเงิน (฿)</th>
                        <th class="text-center" style="width:80px;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $idx => $voucher)
                        @php
                            $grand_total += (float) $voucher->total_amount;
                            $status_badge_map = [
                                'draft'     => 'badge-secondary',
                                'pending'   => 'badge-warning',
                                'reviewed'  => 'badge-info',
                                'verified'  => 'badge-info',
                                'approved'  => 'badge-success',
                                'completed' => 'badge-success',
                                'rejected'  => 'badge-danger',
                            ];
                            $badge_class = $status_badge_map[$voucher->status] ?? 'badge-secondary';
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <a href="{{ $type === 'rv' ? route('receipt-voucher.show', $voucher->id) : route('payment-voucher.show', $voucher->id) }}"
                                   class="voucher-link" target="_blank">
                                    {{ $voucher->voucher_no }}
                                </a>
                            </td>
                            <td>{{ optional($voucher->voucher_date)->format('d/m/Y') }}</td>
                            <td>{{ $type === 'rv' ? ($voucher->receive_from ?? '-') : ($voucher->pay_to ?? '-') }}</td>
                            <td class="text-muted">{{ Str::limit($voucher->remark ?? $voucher->description ?? '', 45) }}</td>
                            <td class="text-right amount-cell">{{ number_format($voucher->total_amount, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $badge_class }}">{{ $voucher->status_label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 32px;">
                                ไม่พบรายการในช่วงเวลาที่เลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Summary --}}
            @if(count($vouchers) > 0)
                <div class="summary-section">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span class="label">จำนวนรายการทั้งหมด</span>
                            <span class="value">{{ number_format(count($vouchers)) }} รายการ</span>
                        </div>
                        <div class="summary-row total">
                            <span class="label">ยอดเงินรวมทั้งสิ้น</span>
                            <span class="value">฿{{ number_format($grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Doc Footer --}}
            <div class="doc-footer">
                <span>
                    InfiniteBrain Backoffice — ระบบการเงิน
                </span>
                <span>
                    พิมพ์เมื่อ {{ now()->locale('th')->translatedFormat('d M Y เวลา H:i น.') }}
                </span>
            </div>

        </div>{{-- .a4-page --}}
    </div>{{-- .page-wrapper --}}

</body>
</html>
