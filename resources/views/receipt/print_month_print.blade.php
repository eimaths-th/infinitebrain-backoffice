<!DOCTYPE html>
<html lang="en">

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
    </style>
</head>

<body>
    <div class="row">


        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                


                <table align="center" width="95%" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <td width="15%" rowspan="4"><img src="{{ asset('images/infinite.jpg') }}" alt="..."
                                    height="106" style="padding-bottom: 5px"></td>
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
                    <table align="center" width="95%" cellpadding="0" cellspacing="0">
                        <tr class="active" height="30">
                            <td align="center" width="15%" class="active_left grey"></td>
                            <td align="center" width="40%">
                                <font class="grey">รวมรายรับ </font> <b>{{ $sfielda[$sfield] }}</b> <b>{{ $thaimonth }} {{ $thaiyear }} </b>
                            </td>
                            <td align="center" width="15%"></td>
                            <td align="center" width="15%">
                                <font class="grey">รหัสเอกสาร</font>
                            </td>
                            <td align="center" width="15%" class="active_right"><b>{{ $sfieldas[$sfield] }}{{ $ymcode }}</b></td>
                        </tr>

                    </table>

                    <table align="center" width="95%" cellpadding="0" cellspacing="0">
                        <tr class="actived activebg" height="30">
                            <td align="center" width="15%" class="actived grey">วัน / เดือน / ปี</td>
                            <td align="center" width="30%">
                                <font class="actived grey">รายการ</font>
                            </td>
                            <td align="center" width="20%" class="grey actived">เอกสารอ้างอิง</td>
                            <td align="center" width="15%">
                                <font class="grey actived">รายรับ</font>
                            </td>
                            <td align="center" width="15%" class="actived gray">หน่วย</td>
                        </tr>
                        @foreach ($data as $receipt)
                        <tr class="activeda" height="30">
                            <td align="center" width="15%" class="actived"><b><font size="2">{{ $receipt['receipt_submission_date'] }}</font></b></td>
                            <td align="left" width="40%" class="actived">&nbsp; <b><font size="2">{{ $receipt['description'] }}</font></b></td>
                            <td align="center" width="15%" class="actived"><b><font size="2">{{ $receipt['receipt_numbers'] }}</font></b></td>
                            <td align="center" width="15%" class="actived"><b><font size="2">{{ $receipt['total_price'] }}</font></b></td>
                            <td align="center" width="15%" class="actived grey"><b><font size="2">{{ $receipt['receipt_price_label'] }}</font></b></td>
                        </tr>
                        @endforeach
                        <tr class="activebg" height="50">
                            <td align="center" colspan="3" width="15%" class="actived activebottomd grey"><font size="3"><b>รวมทั้งสิ้น</b></font></td>
                            <td align="center" width="15%" class="actived activebottomd"><b>{{ $total_price }}</b></td>
                            <td align="center" width="15%" class="actived activebottomd grey"><font size="3"><b>บาท</b></font></td>
                        </tr>
                    </table>
                    
                
              

                    <br><br><br>

                    <table align="center" width="95%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td></td>
                            <td></td>
                            <td align="center">ลงชื่อ...................................</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td align="center">ผู้จัดการ</td>
                        </tr>
                    </table>


                </div>
            </div>

        </div>
</body>


<script>
    function printrep() {
        window.print();
    }
    printrep();
    setTimeout(function() {
        window.close();
    }, 4000);
</script>
