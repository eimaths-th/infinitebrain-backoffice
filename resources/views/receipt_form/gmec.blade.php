@extends('layouts.form')

@section('style')
    <style>
        :root {
            --color-default: grey;
            --color-invalid: red;
            --color-valid: green;
        }

        input {
            border: 2px solid var(--color-default);
            /*text-align: center;*/
        }

        .error-message {
            display: none;
            font-weight: bold;
            color: var(--color-invalid);
        }

        /* To demonstrate that the validations only happen on blur, we apply this malpractice */
        input:focus {
            outline: none;
        }

        /*fucus empty color*/
        /*input:required:focus:invalid {
                                background: url("https://assets.digitalocean.com/labs/icons/exclamation-triangle-fill.svg") no-repeat 95% 40% rgb(247, 213, 213);
                                background-size: 15px;
                            }*/

        input:required:focus:invalid~.form-error-message {
            display: block;
        }

        /* Only show invalid ring while not focused
                                                    input:not(:focus):not(:placeholder-shown):invalid {
                                                        border-color: var(--color-invalid);
                                                    }*/

        /*input:not(:focus):not(:placeholder-shown):invalid~.error-message {
                                                        display: block;
                                                    }*/

        /* Only show valid ring while not focused and if a value is entered */
        /* :empty won't work here as that targets elements that have no childeren. Therefore we abuse :placeholder-shown */
        input:not(:focus):not(:placeholder-shown):valid {
            border-color: var(--color-valid);
        }

        body {
            background-color: #112d53;
        }

        .form_header {
            color: #082d5f;
        }

        .form_sub_header {
            color: #12458D;
        }

        hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px solid #112d53;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -5px;
            margin-left: -5px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 10px;
            padding-left: 15px;
            padding-right: 15px;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .form-group>[class*=col-] {
            padding-right: 5px;
            padding-left: 5px;
        }

        .form-row>.col,
        .form-row>[class*=col-] {
            padding-right: 5px;
            padding-left: 5px
        }

        .div_center {
            margin: auto;

        }

        .form-sub-label {
            color: #224779;
        }

        .form-sub-label {
            font-size: .75em;
            margin-top: 11px;
            margin-left: 2px;
            display: block;
            word-break: break-word;
        }

        .form-label.form-label-auto {
            display: block;
            float: none;
            text-align: left;
            width: 100%;
            color: #082d5f;
        }

        .form-label-top {
            width: 100%;
            margin-left: 2px;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .form-label {
            display: inline-block;
            font-weight: 500;
            word-break: break-word;
        }

        .form-required {
            color: #f23a3c;
        }

        .form-required {
            margin-left: 4px;
        }

        .form-group:focus-within {
            background-color: white;
        }

        .form-focus-error {
            background-color: #f8cccd;
        }

        .bg-light2 {
            background-color: #e4efff;
        }

        .form-error-message {
            color: #fff;
            background-color: #f23a3c;
            background-image: url(data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='15' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M7 14.515a7 7 0 110-14 7 7 0 010 14zm-.814-5.86h1.628v-5.21H6.186v5.21zM7 11.259a.82.82 0 00.814-.824A.816.816 0 007 9.631a.813.813 0 100 1.628z' fill='%23fff'/%3E%3C/svg%3E);
            font-size: .75em;
            margin-top: 8px;
            border-radius: 4px;
            background-size: 0.875em;
            background-position: 0.375em;
            background-repeat: no-repeat;
            display: none;
            width: 150px;
            padding: 0.25em 0.5em 0.25em 1.625em;
        }
    </style>
@endsection


@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card bg-light2 mb-3 ">
                    {{-- <div class="card-header">{{ __('Login') }}</div> --}}
                    <div align="center"><img class="card-img" src="{{ asset('images/BG_0.63c0ecacd51e91.88109489.jpg') }}"
                            alt="..." height="360"></div>
                    <div class="card-body">
                        <br>
                        <div class="text-center">
                            <h2 class="card-title form_header">GMEC 2023 Registration Form</h2>
                            <p class="card-text form_sub_header"><b>Fill out the form carefully for registration</b></p>
                        </div>
                        <br>
                        <hr />

                        <div style="padding-left: 50px" id="cid_48" class="form-input-wide" data-layout="full">
                            <div id="text_48" class="form-html" data-component="text" tabindex="0">
                                <p style="text-align: center;"><span style="color: #6351a2;"><strong><span
                                                style="text-decoration: underline;">GMEC 2023 Registration
                                                Form</span></strong></span></p>
                                <p style="text-align: left;"><span style="color: #6351a2;"><strong>Registration
                                            Date:</strong> 15 January - 3 March 2023</span></p>
                                <p><span style="color: #6351a2;"><strong>Exam Date:</strong>&nbsp;25 March&nbsp;2023</span>
                                </p>
                                <p><span style="color: #6351a2;"><strong>Result Release:&nbsp;</strong>19 April 2023</span>
                                </p>
                                <p><span style="color: #6351a2;"><strong>Venue:</strong>&nbsp;Online Competition</span></p>
                                <p><strong><span style="color: #6351a2;">Registration Fee:&nbsp;</span></strong></p>
                                <p><span style="color: #6351a2;"><strong>Test:</strong> 790 baht (1
                                        category)</span><br><span style="color: #6351a2;">1290 baht (2 categories)</span>
                                </p>
                                <p><span style="color: #6351a2;"><strong>Test+tutoring course:</strong> </span><span
                                        style="color: #6351a2;">1090 bath (1 class/ 1 category)</span><br><span
                                        style="color: #6351a2;">1790 baht(2 classes/ 2 categories: 1 class for basic math
                                        and 1 class for olympiad math)</span></p>
                                <p>&nbsp;</p>
                                <p><span style="color: #6351a2;">&nbsp;</span></p>
                            </div>
                        </div>

                        <div class="col-md-10 div_center">
                            <form id="infinite" method="POST" action="{{ route('login.custom') }}">
                                @csrf

                                <div class="row mb-3 ">
                                    <label for="success" class="col-md-4 col-form-label text-md-end"></label>

                                    <div class="col-md-6">

                                        @if ($message = Session::get('success'))
                                            <div class="alert alert-success">
                                                <p>{{ $message }}</p>
                                            </div>
                                        @endif

                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <p>{{ $errors->first() }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-row form-focus">
                                    <label style="padding-left: 20px"
                                        class="form-label form-label-top form-label-auto">Name-Surname (English) /
                                        ชื่อ - นามสกุล ภาษาอังกฤษ <span class="form-required">*</span></label>
                                    <div class="col-md-6">

                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="firstname"></label>
                                            <input type="text" class="form-control" name="firstname" id="firstname"
                                                value="" maxlength="50" required>
                                            <label class="form-sub-label" style="min-height:13px" aria-hidden="false">First
                                                Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="lastname"></label>
                                            <input type="text" class="form-control" name="lastname" id="lastname"
                                                value="" maxlength="50" required>
                                            <label class="form-sub-label" style="min-height:13px" aria-hidden="false">Last
                                                Name</label>
                                            {{-- <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-focus">

                                    <div class="col-md-6">

                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="nickname">Nickname
                                                (English)/ ชื่อเล่น (ภาษาอังกฤษ)</label>
                                            <input type="text" class="form-control" name="nickname" id="Addnickname"
                                                value="" maxlength="50" required>
                                            <label class="form-sub-label" style="min-height:13px" aria-hidden="false">Nick
                                                Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="birthdate">Birth
                                                Date / วันเกิด</label>
                                            <input type="date" class="AddDate form-control" name="birthdate"
                                                id="AddBirthdate" value="" maxlength="10" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Date</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-focus">

                                    <div class="col-md-6">

                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="schoolname">School name (English) / ชื่อโรงเรียน (ภาษาอังกฤษ)</label>
                                            <input type="text" class="form-control" name="schoolname"
                                                id="Addschoolname" value="" maxlength="50" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">School Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="grade">Grade
                                                / ระดับชั้น</label>
                                            <input type="date" class="AddDate form-control" name="grade"
                                                id="AddGrade" value="" maxlength="10" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Grade</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-row form-focus">

                                    <div class="col-md-6">

                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="schoolname">แพคเกจในการสอบ / Packages</label>
                                            <input type="text" class="form-control" name="schoolname"
                                                id="Addschoolname" value="" maxlength="50" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Packages</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="grade">Parent's name / ชื่อและนามสกุลผู้ปกครอง</label>
                                            <input type="date" class="AddDate form-control" name="grade"
                                                id="AddGrade" value="" maxlength="10" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Parent's name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-focus">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Name">Address / ที่อยู่ภาษาไทยสำหรับจัดส่งของรางวัล <span
                                                    class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="address" id="AddAddress"
                                                required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Street Address</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10">
                                                <span class="error-navigation-message">This field is required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Name"></label>
                                            <input type="text" class="form-control" name="address2" id="AddAddress2"
                                                required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Postal / Zip Code / รหัสไปรษณีย์</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10">
                                                <span class="error-navigation-message">This field is required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-md-6 form-focus">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Submission Date">Submission Date: <span
                                                    class="form-required">*</span></label>
                                            <input type="date" class="AddDate form-control" name="submission_date"
                                                id="AddSubmissiondate" value="" maxlength="10" required>
                                            <input type="hidden" name="Sfield" id="Sfield" value="2">
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-6 form-focus">
                                        <div class="position-relative  form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Package">Package:
                                                <span class="form-required">*</span></label>
                                            <input type="email" class="form-control" name="package" id="AddPackage"
                                                value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-md-6 form-focus">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="Grade">Grade:
                                                <span class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="grade" id="AddGrade"
                                                value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-focus">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Des">Description:</label>
                                            <input type="text" class="form-control" name="description" id="AddDes"
                                                value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-row">
                                    <div class="col-md-6 form-focus">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto" for="Parent">Name
                                                Parent: <span class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="name_parent" id="AddParent"
                                                value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-focus">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Mobile">Mobile: <span class="form-required">*</span></label>
                                            <input type="text" class="AddPhone form-control" name="mobile"
                                                id="AddMobile" value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row form-focus">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="Postcode">Postcode:
                                                <span class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="postcode" id="AddPostcode"
                                                value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10">
                                                <span class="error-navigation-message">This field is required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <div class="form-row form-focus">

                                </div>

                                <div class="form-row form-focus">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto" for="File">File:
                                                <span class="form-required">*</span></label>
                                            <input type="text" class="form-control" id="AddFile" name="file"
                                                value="" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">First Name</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10">
                                                <span class="error-navigation-message">This field is required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var inputlist = ["firstname", "lastname", "address", "postcode"];
            $("input").focusout(function() {
                let inputxx;
                let nameinput = $(this).attr("name");

                if (inputlist.indexOf($(this).attr("name")) >= 0) {
                    inputxx = ".form-focus";
                } else {
                    inputxx = ".form-group";
                }

                if (nameinput == 'firstname') {
                    if ($(this).val() == '' || $("#lastname").val() == '') {
                        //alert();
                        $(this).closest(inputxx).addClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                        if ($(this).val() == '') {
                            $(this).css('border', 'solid 1px red');
                        } else {
                            $(this).css('border', 'solid 1px green');
                        }

                        if ($("#lastname").val() == '') {
                            $("#lastname").css('border', 'solid 1px red');
                        } else {
                            $("#lastname").css('border', 'solid 1px green');
                        }

                        //$(this).parents().css( "background-color", "red" );
                    } else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $("#lastname").css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }

                } else if (nameinput == 'lastname') {
                    if ($(this).val() == '' || $('#firstname').val() == '') {
                        //alert();
                        $(this).closest(inputxx).addClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                        if ($(this).val() == '') {
                            $(this).css('border', 'solid 1px red');
                        } else {
                            $(this).css('border', 'solid 1px green');
                        }

                        if ($("#firstname").val() == '') {
                            $("#firstname").css('border', 'solid 1px red');
                        } else {
                            $("#firstname").css('border', 'solid 1px green');
                        }
                        //$(this).parents().css( "background-color", "red" );
                    } else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $("#firstname").css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }

                } else {
                    if ($(this).val() == '') {
                        $(this).closest(inputxx).addClass('form-focus-error'); $(this).closest(inputxx)
                        .find('.form-error-message').css("display", "block");
                        $(this).css('border', 'solid 1px red');
                        //$(this).parents().css( "background-color", "red" );
                    }
                    else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }
                }
            });
            //.trigger("focusout");

            /* $("#infinite").validate({
                onfocusout: function(element) {
                    //this.element(element);
                    element.parent().css( "background-color", "red" );
                },
            });*/

        });
    </script>
@endsection
