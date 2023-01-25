<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <style>
        table {
            font-size: 0.8rem;
        }

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

        .tbr table td.activetop {
            border-top: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.active_twin {
            border-left: 2px solid rgb(138, 175, 61);
            border-right: 2px solid rgb(138, 175, 61);
        }
        .tbr table td.active {
            
            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activebottom {
            border-bottom: 2px solid rgb(138, 175, 61);
        }
    </style>
</head>

<body>
    <div class="row">

        
        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">
                <table align="center" width="98%" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <td width="20%" rowspan="4" align="center"><img src="{{ asset('images/infinite.jpg') }}" alt="..."
                                    height="106" style="padding-bottom: 5px"></td>
                            <td width="80%" height="34">บริษัท อินฟินิท เบรน จำกัด (สำนักงานใหญ่) </td>
                        </tr>
                        <tr>
                            <td height="26">555/9 ม.1 ต.บางขนุน อ.บางกรวย จ.นนทบุรี 11130</td>
                        </tr>
                        <tr>
                            <td height="30">เลขประจำตัวผู้เสียภาษี: 0125563031038</td>
                        </tr>
                        <tr>
                            <td>โทร 098 509 8554</td>
                        </tr>
                    </thead>
                </table>

                <table align="center" width="98%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="4" align="center"><b>ใบกำกับภาษี/ใบเสร็จรับเงิน</b></td>
                        <td  align="center" class="grey"><b>ต้นฉบับ <br> สำหรับลูกค้า</b></td>
                    </tr>
                    <tr>
                        <td width="21%" class="grey">นามลูกค้า <br>ที่อยู่/Client,Address</td>
                        <td align="right" width="21%" class="grey">รหัสลูกค้า/Customer's Code</td>
                        <td>&nbsp;<b> {{ $data->receipt_customer_number }} </b></td>
                        <td align="right" width="18%" class="grey">เลขที่ / Voucher No.</td>
                        <td width="15%">&nbsp;<b> {{ $data->receipt_number }} </b></td>
                    </tr>
                    <tr>
                        <td><font class="grey">คุณ</font> <b> {{ $data->name_parent }} </b></td>
                        <td align="right" class="grey">ชื่อนักเรียน</td>
                        <td align="left" width="25%">&nbsp;<b>{{ $data->first_name }} {{ $data->last_name }}</b></td>
                        <td align="right" class="grey">วันที่ / Date</td>
                        <td>&nbsp;<b>{{ $data->submission_date }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="3" ><font class="grey">ที่อยู่</font> <b>{{ $data->street_address }} {{ $data->street_address2 }}
                            {{ $data->city }} {{ $data->province }} {{ $data->postcode }} </b></td>

                        <td align="right" class="grey">ชำระเงิน / Credit Term</td>
                        <td>..........................</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>

                        <td align="right" class="grey">ครบกำหนด / Due Date</td>
                        <td>..........................</td>
                    </tr>
                    <tr>
                        <td colspan="5" ><font class="grey">โทร.</font> <b> {{ $data->mobile }} </b></td>

                    </tr>
                </table>
                <div class="tbr">
                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr class="active">
                            <td align="center" width="30%" class="grey">ลำดับที่ / NO.</td>
                            <td align="center" class="grey">รายการ / Description</td>
                            <td align="center" class="grey">จำนวนเงิน / Amount</td>
                        </tr>
                        <tr>
                            <td align="center" style="vertical-align: top;">1</td>
                            <td align="left">{!! $des_text !!}</td>
                            <td align="center" style="vertical-align: top;">{{ $price_val }}</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="20%" class="activetop" >
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td colspan="2" class="active_twin activebottom grey" align="center">จ่ายด้วย</td>

                                    </tr>
                                    <tr>
                                        <td class="active_twin activebottom" width="30%"></td>
                                        <td class="active activebottom grey" height="25" >1.เงินสด</td>
                                    </tr>
                                    <tr>
                                        <td class="active_twin activebottom" width="30%" align="center"><img src="{{ asset('images/close.png') }}" alt="..."
                                            height="14" style="padding-bottom: 1px"></td>
                                        <td class="active activebottom grey" height="25">2.โอนเงิน</td>
                                    </tr>
                                    <tr>
                                        <td class="active_twin" width="30%"></td>
                                        <td class="active grey" height="25">3.เครดิต</td>
                                    </tr>
                                </table>
                            </td>
                            <td colspan="3" class="activetop">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="30%"></td>
                                        <td width="20%"></td>
                                        <td width="30%" class="grey">มูลค่าบริการ/Sub Total</td>
                                        <td width="20%"> <b> {{ $vat_price_val }} </b></td>
                                    </tr>
                                    <tr>
                                        <td width="30%"></td>
                                        <td width="20%"></td>
                                        <td width="30%"></td>
                                        <td width="20%"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%"></td>
                                        <td width="20%"></td>
                                        <td width="30%"class="grey" >ส่วนลดบริการ/Discout</td>
                                        <td width="20%" > - </td>
                                    </tr>
                                    <tr>
                                        <td width="30%"></td>
                                        <td width="20%"></td>
                                        <td width="30%" class="grey">ภาษีมูลค่าเพิ่ม Va 7%</td>
                                        <td width="20%"><b>{{ $vat_val }}</b></td>
                                    </tr>
                                </table>
                            </td>

                        </tr>
                        <tr class="active">
                            <td align="center" width="30%" class="grey">รวมทั้งสิ้น/Grand Total</td>
                            <td align="center"><b>{{ $price_text }}</b></td>
                            <td align="center"><b>{{ $price_val }}</b></td>
                            <td align="right" class="grey">บาท</td>
                        </tr>
                    </table>

                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center" class="grey">ได้รับริการตามบริการถูกต้องแล้ว</td>
                            <td align="center" class="grey">ในนามบริษัท อินฟินิท เบรน จำกัด</td>
                        </tr>
                    </table>

                    <table align="center" width="98%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="center">.........................</td>
                            <td align="center">.........................</td>
                            <td align="center">.........................</td>
                        </tr>
                        <tr>
                            <td align="center" class="grey">ผู้รับบริการ</td>
                            <td align="center" class="grey">ผู้ให้บริการ</td>
                            <td align="center" class="grey">ผู้จัดการ</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="center">.........................</td>
                            <td align="center">.........................</td>
                            <td align="center">.........................</td>
                        </tr>
                        <tr>
                            <td align="center" class="grey">วันที่</td>
                            <td align="center" class="grey">วันที่</td>
                            <td align="center" class="grey">วันที่</td>
                        </tr>
                    </table>
                    <center>{!! $file_att !!}</center>
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
