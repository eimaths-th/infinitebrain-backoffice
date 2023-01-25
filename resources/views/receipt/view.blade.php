@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        .tbr table td.activetop {
            border-top: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.active {
            border-left: 2px solid rgb(138, 175, 61);
            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activebottom {
            border-bottom: 2px solid rgb(138, 175, 61);
        }
    </style>
@endsection

@section('content')
    <div class="row">

        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-note2 icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        Receipts
                        <div class="page-title-subheading">Infinite Brain Receipts Data</div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">

                        <a class="btn btn-warning " href="#" onclick="export_report();"><i class="fa fa-print"></i>
                            Print</a>
                        <a href="{{ route('receipts', ['sfield' => $data->sfield]) }}" class="btn btn-success"
                            data-toggle="modal" data-target=".bd-example-modal-lg" id="CreateButton"><i
                                class="fa fa-backward"></i> Back
                            Receipts</a>


                    </div>
                </div>
            </div>
        </div>

        <div class="main-card mb-3 card " id="x_content">
            <div class="card-body">


                <table align="center" width="85%" cellpadding="0" cellspacing="0">
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

                <table align="center" width="85%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="4" align="center"><b>ใบกำกับภาษี/ใบเสร็จรับเงิน</b></td>
                        <td align="center" class="grey"><b>ต้นฉบับ <br> สำหรับลูกค้า</b></td>

                    </tr>
                    <tr>
                        <td class="grey">นามลูกค้า,ที่อยู่/Client,Address</td>
                        <td align="right" class="grey">รหัสลูกค้า/Customer's Code</td>
                        <td> <b> {{ $data->receipt_customer_number }} </b></td>
                        <td align="right" class="grey">เลขที่ / Voucher No.</td>
                        <td> <b> {{ $data->receipt_number }} </b></td>
                    </tr>
                    <tr>
                        <td><font class="grey">คุณ</font> <b> {{ $data->name_parent }} </b></td>
                        <td align="right" class="grey">ชื่อนักเรียน</td>
                        <td align="left" width="30%"> <b>{{ $data->first_name }} {{ $data->last_name }}</b></td>
                        <td align="right" class="grey">วันที่ / Date</td>
                        <td> <b>{{ $data->submission_date }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="3"><font class="grey">ที่อยู่</font> <b>{{ $data->street_address }} {{ $data->street_address2 }}
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
                        <td colspan="5"><font class="grey">โทร.</font> <b> {{ $data->mobile }} </b></td>

                    </tr>
                </table>
                <div class="tbr">
                    <table align="center" width="85%" cellpadding="0" cellspacing="0">
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
                    <table align="center" width="85%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="20%" class="activetop">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td colspan="2" class="active activebottom grey" align="center">จ่ายด้วย</td>

                                    </tr>
                                    <tr>
                                        <td class="active activebottom" width="30%"></td>
                                        <td class="active activebottom grey">1.เงินสด</td>
                                    </tr>
                                    <tr>
                                        <td class="active activebottom" width="30%" align="center"><i
                                                style="font-size: 1.8em; color:red" class="fa fa-close"></i></td>
                                        <td class="active activebottom grey">2.โอนเงิน</td>
                                    </tr>
                                    <tr>
                                        <td class="active" width="30%"></td>
                                        <td class="active grey">3.เครดิต</td>
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
                                        <td width="30%" class="grey">ส่วนลดบริการ/Discout</td>
                                        <td width="20%"></td>
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
                            <td align="center" class="grey">รวมทั้งสิ้น/Grand Total</td>
                            <td align="center"><b>{{ $price_text }}</b></td>
                            <td align="center"><b>{{ $price_val }}</b></td>
                            <td align="right" class="grey">บาท</td>
                        </tr>
                    </table>

                    <table align="center" width="85%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center" class="grey">ได้รับริการตามบริการถูกต้องแล้ว</td>
                            <td align="center" class="grey">ในนามบริษัท อินฟินิท เบรน จำกัด</td>
                        </tr>
                    </table>

                    <table align="center" width="85%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
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
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
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

    </div>
@endsection

@section('script')
    <script>
        function export_report() {
            window.open('{{ route('receipts.show', ['type' => 'print', 'id' => $id]) }}');
        }
    </script>
@endsection
