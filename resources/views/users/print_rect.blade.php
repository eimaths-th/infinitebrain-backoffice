<!DOCTYPE html>
<html lang="en">
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        table td.grey {
            color: rgb(141, 138, 138);

        }

        font.grey {
            color: rgb(141, 138, 138);

        }

        .tbr table tr.active td {
            border-top: 2px solid rgb(138, 175, 61);
            border-bottom: 2px solid rgb(138, 175, 61);
        }

        .tbr table tr.activebg td {
            background: #c9fcb9;
        }

        .tbr table tr.actived td {
            border-bottom: 1px solid rgb(7, 7, 7);

        }

        .tbr table tr.activeda td {
            /* border-bottom: 1px solid rgb(7, 7, 7); */
            border-top: 1px solid rgb(7, 7, 7);

        }

        .tbr table tr.activedab td {
            border-bottom: 1px solid rgb(7, 7, 7);
            border-top: 1px solid rgb(7, 7, 7);

        }

        .tbr table td.activetop {
            border-top: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.active {
            border-left: 2px solid rgb(138, 175, 61);
            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.actived {
            border-left: 1px solid rgb(7, 7, 7);
            /* border-right: 1px solid rgb(7, 7, 7); */
        }

        .tbr table td.activedr {
            border-left: 1px solid rgb(7, 7, 7);
            border-right: 1px solid rgb(7, 7, 7);
        }

        .tbr table td.activedright {
            border-right: 1px solid rgb(7, 7, 7);
        }

        .tbr table td.active_left {
            border-left: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.active_right {
            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activebottom {
            border-bottom: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activebottomd {
            border-bottom: 1px solid rgb(7, 7, 7);
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: rgb(97, 240, 166) !important;
        }
        .text-danger {
            color: #d92550 !important;
        }
        .text-warning {
            color: #f8d821 !important;
        }
        .text-primary {
            color: #3f6ad8 !important;
        }
        .text-dark {
            color: rgb(22, 22, 22) !important;
        }

        /* Signature Styles */
        .signature-section {
            margin-top: 30px;
        }
        .signature-box {
            text-align: center;
            min-height: 80px;
            position: relative;
        }
        .signature-image {
            max-height: 60px;
            max-width: 150px;
        }
        .signature-line {
            border-bottom: 1px dotted #333;
            width: 150px;
            display: inline-block;
            min-height: 60px;
        }
        .signature-pad-container {
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            display: inline-block;
        }
        #employeeSignaturePad {
            width: 200px;
            height: 80px;
        }
        .signature-controls {
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .signature-pad-container {
                border: none;
            }
        }
    </style>
    {{-- <style>
        @media print {
            @page {
                size: landscape;
            }
        }
    </style> --}}
</head>

<body>
    <div class="row">

        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <table align="center" width="98%" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <td width="15%" rowspan="4"><img src="{{ asset('images/infinite.jpg') }}"
                                    alt="..." height="106" style="padding-bottom: 5px"></td>
                            <td>บริษัท อินฟินิท เบรน จำกัด (สำนักงานใหญ่) </td>
                        </tr>
                        <tr>
                            <td>555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</td>
                        </tr>
                        <tr>
                            <td>เลขประจำตัวผู้เสียภาษี: 0125563031038</td>
                        </tr>
                        <tr>
                            <td>โทร 098 509 8554</td>
                        </tr>
                    </thead>
                </table>

                <div class="tbr">
                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr class="active" height="30">
                            <td align="center" width="10%" class="active_left grey"></td>
                            <td align="center" width="80%" colspan="5">

                                <font class="grey"> Record Time Infinite Brain </font> {{ $name }}
                                @if(isset($search_type) && $search_type === 'daterange')
                                    {{ $date }}
                                @else
                                    {{ date('F-Y', strtotime("$year-$month")) }}
                                @endif
                                <b></b>
                            </td>
                            {{-- <td align="center" width="10%"><b></b></td>
                            <td align="center" width="10%">
                                <font class="grey"></font>
                            </td> --}}
                            <td align="center" width="10%" class="active_right">
                                <b></b></b>
                            </td>
                        </tr>

                    </table>

                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        {{-- <thead> --}}
                        <tr class=" activebg" height="30">
                            <td class="actived grey" align="center">No.</td>
                            <td class="actived grey" align="center">Code</td>
                            <td class="actived" align="center">
                                <font class="grey">Name</font>
                            </td>
                            <td class="actived" align="center">
                                <font class="grey">
                                    Date
                                </font>
                            </td>
                            <td align="center" class="grey actived"> Time</td>
                            <td class="actived" align="center">
                                <font class="grey">Status</font>
                            </td>
                            <td align="center" class="activedr gray">Detail</td>
                        </tr>
                        {{-- </thead> --}}

                        {{-- <tbody> --}}
                        @php
                            $resul_late_time = 0;
                            $resul_late = 0;
                            $resul_missing = 0;
                            $resul_notIn = 0;
                            $resul_notOut = 0;
                            $resul_leaveFirst = 0;
                        @endphp
                        @foreach ($rec_times as $key => $rec_time)
                            @php

                            @endphp
                            <tr class="activeda " height="15">
                                <td align="center" class="actived">
                                    <b>
                                        <font size="{{ $fontsize }}">
                                            {{ $key + 1 }}
                                        </font>
                                    </b>
                                </td>
                                <td align="center" class="actived">
                                    <b>
                                        <font size="{{ $fontsize }}">
                                            INF{{ $rec_time->user->code }}
                                        </font>
                                    </b>
                                </td>
                                <td align="left" class="actived">&nbsp;
                                    <b>
                                        <font size="{{ $fontsize }}">{{ $rec_time->user->name }}
                                        </font>
                                    </b>
                                </td>
                                <td align="center" class="actived">
                                    <b>
                                        <font size="{{ $fontsize }}">
                                            {{ date('D d-M-Y', strtotime($rec_time->date)) }}
                                        </font>
                                    </b>
                                </td>
                                <td align="center"class="actived">
                                    <b>
                                        <font size="{{ $fontsize }}">
                                            {{ $rec_time->full_time }}
                                        </font>
                                    </b>
                                </td>
                                @php
                                    $class = '';
                                    switch ($rec_time->status) {
                                        case 1:
                                            $class = 'success';
                                            break;

                                        case 2:
                                        case 3:
                                        case 4:
                                        case 13:
                                            $class = 'danger';
                                            break;

                                        case 5:
                                        case 6:
                                        case 7:
                                        case 8:
                                        case 9:
                                            $class = 'warning';
                                            break;

                                        case 11:
                                        case 12:
                                            $class = 'primary';
                                            break;

                                        case 10:
                                            $class = 'dark';

                                            break;
                                        case 14:
                                        case 15:
                                            $class = 'dark';
                                            break;
                                        case 16:
                                            $class = 'info';
                                            break;
                                        case 17: // ทำงานนอกสถานที่
                                            $class = 'secondary';
                                            break;

                                        default:
                                            break;
                                    }
                                @endphp
                                <td align="center" class="actived">
                                    <b>
                                        <font size="{{ $fontsize }}">
                                            <span
                                                class="text-{{ $class }} align-items-center">
                                                {{ $rec_time->status_name }}</span>
                                        </font>
                                    </b>
                                </td>
                                <td align="center" class="activedr grey">
                                    <b>
                                        <font size="{{ $fontsize }}">
                                            @php
                                                $text_late = '';
                                                if ($rec_time->late_time) {
                                                    $text_late = '| สาย : ' . $rec_time->late_time . ' นาที';
                                                }
                                                $resul_late_time += $rec_time->late_time;

                                                // $resul_missing = 0;

                                            @endphp
                                            {{ $rec_time->explain }} {{ $text_late }}
                                        </font>
                                    </b>
                                </td>
                            </tr>
                            @php
                                if ($rec_time->status == 5) {
                                    # code...
                                    $resul_late++;
                                }
                                if ($rec_time->status == 13) {
                                    # code...
                                    // $resul_late++;
                                    $resul_missing++;
                                }
                                if ($rec_time->status == 6) {
                                    # code...
                                    // $resul_late++;
                                    $resul_notIn++;
                                }
                                if ($rec_time->status == 7) {
                                    # code...
                                    // $resul_late++;
                                    $resul_notOut++;
                                }
                                if ($rec_time->status == 9) {
                                    # code...
                                    // $resul_late++;
                                    $resul_leaveFirst++;
                                }
                            @endphp
                        @endforeach

                        {{-- <tr class="activeda" height="15">
                            <td align="center" width="1%" class="activeda">

                            </td>
                            <td align="center" width="1%" class="activeda">

                            </td>
                            <td align="left" class="activeda">&nbsp;

                            </td>
                            <td align="center" class="activeda">

                            </td>
                            <td align="center" class="activeda">

                            </td>
                            <td align="center" class="activeda ">

                            </td>
                            <td align="center" class="activeda">

                            </td>
                        </tr> --}}
                        {{-- </tbody> --}}


                        {{-- <tr class="activebg" height="25">
                            <td align="center" colspan="3" width="15%" class="actived activebottomd grey">
                                <font size="{{ $fontsize }}"><b>รวมทั้งสิ้น</b></font>
                            </td>
                            <td align="center" width="15%" class="actived activebottomd">
                                <font size="{{ $fontsize }}"><b></b></font>
                            </td>
                            <td align="center" width="15%" class="activedr activebottomd grey">
                                <font size="{{ $fontsize }}"><b>ชั่วโมง</b></font>
                            </td>
                        </tr> --}}

                    </table>

                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr class="activedab" height="30">
                            <td width="15%" class="actived" align='center'> ขาดงาน : {{ $resul_missing }} &nbsp;=
                            </td>

                            <td width="15%" align='left'>ลาป่วย : </td>

                            <td width="15%" align='left'> ลากิจ : </td>
                            <td width="15%" align='left'> ลาพักร้อน : </td>
                            <td align='right' width="15%"> สาย : {{ $resul_late }} ครั้ง &nbsp;=</td>
                            <td align='left' width="15%" class="activedright">
                                &nbsp;
                                <span align="left">{{ $resul_late_time }}</span>
                                <span align="right">นาที</span>
                            </td>

                        </tr>
                        <tr class="actived" align='left' height="30">
                            {{-- <td>ไม่สแกนเข้า</td>
                            <td>ไม่สแกนออก</td>
                            <td>ออกก่อน</td>
                            <td>ลาครึ่งวัน</td>
                            <td>OT</td>
                            <td>ชดเชย</td> --}}

                            <td width="15%" class="actived" align='center'>ไม่สแกนเข้า : {{ $resul_notIn }}</td>
                            <td width="15%" align='left'> ไม่สแกนออก : {{ $resul_notOut }}</td>
                            <td width="15%" align='left'> ออกก่อน : {{ $resul_leaveFirst }}</td>
                            <td width="15%" align='left'> ลาครึ่งวัน : </td>
                            <td align='center' width="15%"> OT : </td>
                            <td align='left' width="15%" class="activedright"> &nbsp;&nbsp;ชดเชย :
                            </td>
                        </tr>
                    </table>

                    <br><br><br>

                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td></td>
                            <td></td>
                            <td align="center">ลงชื่อ.. {{ auth()->user()->name }} ....</td>
                            <td></td>
                            <td></td>
                            <td align="center">
                                <div class="signature-box">
                                    {{-- แสดงลายเซ็นที่บันทึกไว้หรือเซ็นใหม่ --}}
                                    <div id="savedSignatureContainer" style="display: none;">
                                        <img id="savedSignatureImg" class="signature-image" src="" alt="ลายเซ็น">
                                    </div>
                                    <div id="drawSignatureContainer" style="display: none;">
                                        <div class="signature-pad-container">
                                            <canvas id="employeeSignaturePad"></canvas>
                                        </div>
                                    </div>
                                    <div id="emptySignatureContainer">
                                        <span class="signature-line"></span>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td align="center">ลงชื่อ................................</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td align="center"></td>
                            <td></td>
                            <td></td>
                            <td align="center">(....{{ $name }}....)</td>
                            <td></td>
                            <td></td>
                            <td align="center"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td align="center">ผู้ดำเนินการ</td>
                            <td></td>
                            <td></td>
                            <td align="center">พนักงาน</td>
                            <td></td>
                            <td></td>
                            <td align="center">ฝ่ายบัญชี</td>
                        </tr>
                    </table>

                    {{-- Signature Controls (จะซ่อนตอนพิมพ์) --}}
                    @if(isset($selected_user) && $selected_user)
                    <div class="signature-controls no-print" style="margin-top: 20px;">
                        <table align="center" width="60%">
                            <tr>
                                <td align="center" colspan="3">
                                    <strong>ตัวเลือกลายเซ็นพนักงาน:</strong>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="padding: 10px;">
                                    <button type="button" onclick="useNoSignature()" style="padding: 8px 15px; cursor: pointer;">
                                        ไม่ใส่ลายเซ็น
                                    </button>
                                </td>
                                <td align="center" style="padding: 10px;">
                                    @if($selected_user->signature)
                                    <button type="button" onclick="useSavedSignature()" style="padding: 8px 15px; cursor: pointer; background: #4CAF50; color: white; border: none;">
                                        ใช้ลายเซ็นที่บันทึกไว้
                                    </button>
                                    @else
                                    <button type="button" disabled style="padding: 8px 15px; cursor: not-allowed; opacity: 0.5;">
                                        ไม่มีลายเซ็นที่บันทึกไว้
                                    </button>
                                    @endif
                                </td>
                                <td align="center" style="padding: 10px;">
                                    <button type="button" onclick="useDrawSignature()" style="padding: 8px 15px; cursor: pointer; background: #2196F3; color: white; border: none;">
                                        เซ็นด้วยมือ
                                    </button>
                                </td>
                            </tr>
                            <tr id="clearSignatureRow" style="display: none;">
                                <td align="center" colspan="3" style="padding: 10px;">
                                    <button type="button" onclick="clearSignaturePad()" style="padding: 5px 10px; cursor: pointer;">
                                        ล้างลายเซ็น
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" colspan="3" style="padding-top: 15px;">
                                    <button type="button" onclick="printDocument()" style="padding: 10px 30px; cursor: pointer; background: #ff9800; color: white; border: none; font-size: 16px;">
                                        🖨️ พิมพ์เอกสาร
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    @endif

                </div>
            </div>

        </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    var signaturePad = null;
    var canvas = document.getElementById('employeeSignaturePad');
    
    // Initialize Signature Pad if canvas exists
    if (canvas) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }
    }
    
    // Saved signature URL
    @if(isset($selected_user) && $selected_user && $selected_user->signature)
        @if($selected_user->signature_is_image)
            var savedSignatureUrl = "{{ Storage::url($selected_user->signature) }}";
        @else
            var savedSignatureUrl = "{{ $selected_user->signature }}";
        @endif
    @else
        var savedSignatureUrl = null;
    @endif
    
    function useNoSignature() {
        document.getElementById('savedSignatureContainer').style.display = 'none';
        document.getElementById('drawSignatureContainer').style.display = 'none';
        document.getElementById('emptySignatureContainer').style.display = 'block';
        document.getElementById('clearSignatureRow').style.display = 'none';
    }
    
    function useSavedSignature() {
        if (savedSignatureUrl) {
            document.getElementById('savedSignatureImg').src = savedSignatureUrl;
            document.getElementById('savedSignatureContainer').style.display = 'block';
            document.getElementById('drawSignatureContainer').style.display = 'none';
            document.getElementById('emptySignatureContainer').style.display = 'none';
            document.getElementById('clearSignatureRow').style.display = 'none';
        }
    }
    
    function useDrawSignature() {
        document.getElementById('savedSignatureContainer').style.display = 'none';
        document.getElementById('drawSignatureContainer').style.display = 'block';
        document.getElementById('emptySignatureContainer').style.display = 'none';
        document.getElementById('clearSignatureRow').style.display = 'table-row';
        
        if (signaturePad) {
            setTimeout(function() {
                resizeCanvas();
            }, 100);
        }
    }
    
    function clearSignaturePad() {
        if (signaturePad) {
            signaturePad.clear();
        }
    }
    
    function printDocument() {
        // If using draw signature, convert canvas to image for printing
        if (document.getElementById('drawSignatureContainer').style.display === 'block' && signaturePad && !signaturePad.isEmpty()) {
            var signatureDataUrl = signaturePad.toDataURL();
            var img = document.createElement('img');
            img.src = signatureDataUrl;
            img.className = 'signature-image';
            img.style.maxHeight = '60px';
            img.style.maxWidth = '150px';
            
            // Replace canvas with image for printing
            var container = document.getElementById('drawSignatureContainer');
            container.innerHTML = '';
            container.appendChild(img);
        }
        
        window.print();
    }
    
    // Auto print only if no user selected (fallback behavior)
    @if(!isset($selected_user) || !$selected_user)
    function printrep() {
        window.print();
    }
    printrep();
    setTimeout(function() {
        window.close();
    }, 4000);
    @endif
</script>

</html>