<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $leave_request->GetLeaveTypeName() }} - {{ $leave_request->user->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        body {
            font-family: 'TH Sarabun New', 'Sarabun', sans-serif;
            font-size: 16pt;
            line-height: 1.6;
            color: #000;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 22pt;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
        }

        .date-line {
            text-align: right;
            margin-bottom: 15px;
        }

        .subject-line {
            margin-bottom: 10px;
        }

        .to-line {
            margin-bottom: 15px;
        }

        .content {
            text-indent: 50px;
            text-align: justify;
            margin-bottom: 20px;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
        }

        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: middle;
            text-align: center;
            line-height: 14px;
        }

        .checkbox.checked::after {
            content: "✓";
            font-size: 12pt;
        }

        .signature-section {
            margin-top: 40px;
        }

        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }

        .signature-box.left {
            float: left;
        }

        .signature-box.right {
            float: right;
        }

        .signature-image {
            max-height: 60px;
            margin-bottom: 5px;
        }

        .signature-line {
            border-top: 1px dotted #000;
            width: 200px;
            margin: 0 auto;
            padding-top: 5px;
        }

        .approval-section {
            margin-top: 60px;
            clear: both;
            padding-top: 30px;
            border-top: 1px solid #ccc;
        }

        .approval-box {
            display: inline-block;
            width: 48%;
            vertical-align: top;
        }

        .note-section {
            margin-top: 30px;
            padding: 10px;
            border: 1px solid #ccc;
            font-size: 12pt;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table.info-table td {
            padding: 5px;
            vertical-align: top;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14pt;
        }

        .print-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ พิมพ์ใบลา
    </button>

    <div class="container">
        @if(in_array($leave_request->leave_type, ['sick', 'personal', 'maternity', 'ordination', 'military']))
            @include('leave_requests.prints.general')
        @elseif($leave_request->leave_type === 'vacation')
            @include('leave_requests.prints.vacation')
        @elseif(in_array($leave_request->leave_type, ['compensate', 'swap_day']))
            @include('leave_requests.prints.compensate')
        @elseif($leave_request->leave_type === 'remote_work')
            @include('leave_requests.prints.remote_work')
        @else
            @include('leave_requests.prints.general')
        @endif
    </div>

    <script>
        window.onload = function() {
            // Auto print if needed
            // window.print();
        }
    </script>
</body>
</html>
