@extends('layouts.form')

@section('style')
    @include('forms_manage.css')
@endsection


@section('content')
    <div id="loader-container" style="display: none;">
        <div id="loader" class="spinner"></div>
        <p id="loading-message">Please wait, your request is being processed...</p>
    </div>
    {{-- <div id="loader"></div> --}}
    <div class="error_div" id="error_div"><br>There are errors in this page. Please correct them before moving on.</div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card bg-light2 mb-3 ">
                    {{-- <div class="card-header">{{ __('Login') }}</div> --}}
                    <div align="center">
                        @if (isset($form->brand))
                            <img class="card-img" src="{{ asset('form_manage/brand/' . $form->brand) }}" alt="..."
                                height="360">
                        @else
                        @endif
                    </div>
                    <form id="infinite" method="POST" action="{{ route('form.store') }}" onsubmit="return check_input();">
                        @csrf
                        <div class="card-body">
                            <br>
                            <div class="text-center">
                                <h2 class="card-title form_header">
                                    @if (isset($form->form_name))
                                        {{ $form->form_name }}
                                    @endif
                                    @if (isset($form->title))
                                        {{ $form->title }}
                                    @endif
                                </h2>
                                <p class="card-text form_sub_header">
                                    <b>
                                        @if (isset($form->sub_title))
                                            {{ $form->sub_title }}
                                        @endif
                                    </b>
                                </p>
                            </div>
                            <br>
                            <hr />

                            <div class="col-md-10 div_center" data-layout="full">
                                @if (session('warning'))
                                    <div class="alert alert-warning">
                                        {!! nl2br(e(session('warning'))) !!}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {!! nl2br(e(session('error'))) !!}
                                    </div>
                                @endif
                                <div class="form-html" data-component="text" tabindex="0">
                                    <p style="text-align: center;">
                                        <span style="color: #6351a2;">
                                            <strong>
                                                <span style="text-decoration: underline;">
                                                    @if (isset($form->head_title))
                                                        {{ $form->head_title }}
                                                    @endif
                                                </span>
                                            </strong>
                                        </span>
                                    </p>
                                    @if (isset($form->registration_date_label))
                                        <p style="text-align: left;">
                                            <span style="color: #6351a2;">
                                                <strong>

                                                    {{ $form->registration_date_label }} :

                                                </strong>
                                                &nbsp;
                                                @if (isset($date))
                                                    {{ $date }}
                                                @else
                                                    Today - 25 October 2024 <br>
                                                @endif
                                            </span>
                                        </p>
                                    @endif
                                    @if (isset($form->contest_date_label))
                                        <p>
                                            <span style="color: #6351a2;">
                                                <strong>
                                                    {{ $form->contest_date_label }} :
                                                </strong>
                                                &nbsp;
                                                @if (isset($form->contest_date))
                                                    {{ $form->contest_date }}
                                                @endif

                                            </span>
                                        </p>
                                    @endif

                                    {{--  <p><span style="color: #6351a2;"><strong>Result Release:&nbsp;</strong>19 April
                                            2023</span>
                                    </p> --}}
                                    {{-- <p><span style="color: #6351a2;"><strong>Venue:</strong>&nbsp;Online Competition</span>
                                    </p> --}}
                                    @if (isset($form->registration_fee_label))
                                        <p>
                                            <span style="color: #6351a2;">
                                                <strong>

                                                    {{ $form->registration_fee_label }} :
                                                </strong>
                                                <br>
                                                @foreach ($form->packages as $item)
                                                    <li style="color: #6351a2;">{{ $item->package }}</li>
                                                @endforeach
                                                <br>

                                                {{-- ค่าสมัครสอบพร้อมติว 1250 บาท --}}

                                            </span>
                                        </p>
                                    @endif


                                    @if (isset($form->admission_criteria_text_label))
                                        <p>
                                            <span style="color: #6351a2;">
                                                <b>

                                                    {{ $form->admission_criteria_text_label }}
                                                    {{ $form->form_name }}

                                                </b>
                                                <br>
                                                <br>

                                                @if (isset($form->admission_criteria_text))
                                                    {!! nl2br(e($form->admission_criteria_text ?? ' ')) !!}
                                                @else
                                                @endif
                                            </span>
                                        </p>
                                    @endif
                                    @if (isset($form->admission_criteria_image_label))
                                        <p>
                                            <span style="color: #6351a2;">
                                                <b>

                                                    {{ $form->admission_criteria_image_label }}

                                                    {{ $form->form_name }}

                                                </b>
                                                <br>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="text-center">
                                                            @if (isset($form->admission_criteria_image))
                                                                <img src="{{ asset('form_manage/admission_criteria_image/' . $form->admission_criteria_image) }}"
                                                                    class="custom-image2 my-3" alt="Image 2"
                                                                    hight="20px">
                                                            @else
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                            </span>
                                        </p>
                                    @endif

                                </div>
                            </div>

                            <div class="col-md-11 div_center">


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

                                <input type="hidden" class="form-control" name="subject" id="subject" value=""
                                    maxlength="50" minlength="2" required>
                                <input type="hidden" name="sfield" id="sfield" value="{{ $form->sfield }}">
                                <input type="hidden" name="routeName" id="routeName"
                                    value="{{ request()->route()->getName() }}">
                                <input type="hidden" name="referent_code" id="referent_code"
                                    value="{{ request()->referent_code }}">

                                <div class="form-row form-focus">
                                    {{-- <label style="padding-left: 20px"
                                        class="form-label form-label-top form-label-auto">Name-Surname (English) /
                                        ชื่อ - นามสกุล ภาษาอังกฤษ <span class="form-required">*</span></label> --}}
                                    <div class="col-md-6">
                                        <label style="padding-left: 20px" class="form-label form-label-top form-label-auto">
                                            {{ $form->student_first_name }}
                                            <span class="form-required">*</span>
                                        </label>
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="firstname">
                                                <div style="width: 270px"></div>
                                            </label>
                                            <input type="text" class="form-control" name="firstname" id="firstname"
                                                value="" maxlength="50" minlength="2" required>
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
                                        <label style="padding-left: 20px"
                                            class="form-label form-label-top form-label-auto">{{ $form->student_last_name }}
                                            <span class="form-required">*</span></label>
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="lastname">
                                                <div style="width: 270px"></div>
                                            </label>
                                            <input type="text" class="form-control" name="lastname" id="lastname"
                                                value="" maxlength="50" minlength="2" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Last
                                                Name</label>

                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-focus">

                                    <div class="col-md-6 col-sm-12">

                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="nickname">{{ $form->student_nick_name }} <span
                                                    class="form-required">*</span>
                                                <div style="width: 270px"></div>
                                            </label>
                                            <input type="text" class="form-control" name="nickname" id="nickname"
                                                value="" maxlength="50" minlength="1" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Nick
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
                                    <div class="col-md-6 col-sm-12">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="birthdate">
                                                {{ $form->date_of_birth }} <span class="form-required">*</span>
                                                <div style="width: 270px"></div>
                                            </label>
                                            <input type="date" class="AddDate form-control" name="birthdate"
                                                id="birthdate" value="" required>
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
                                                for="schoolname">{{ $form->student_school_name }}<span
                                                    class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="schoolname" id="schoolname"
                                                value="" maxlength="100" required>
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
                                            <label class="form-label form-label-top form-label-auto" for="grade">
                                                {{ $form->student_grade }}
                                                <span class="form-required">*</span>
                                                <div style="width: 270px"></div>
                                            </label>
                                            <select style="width: 100%" class="form-control" id="grade"
                                                name="grade" data-component="dropdown" aria-label="Grade / ระดับชั้น"
                                                required>
                                                <option value="">Please Select</option>
                                                @foreach ($form->grades as $grade)
                                                    <option value="{{ $grade->grade }}">{{ $grade->grade }}</option>
                                                @endforeach
                                            </select>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">{{-- Grade as per Academic Year 2022 --}}</label>
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
                                            <label class="form-label form-label-top form-label-auto" for="package">
                                                {{ $form->package ?? ' ' }} <span class="form-required">*</span></label>
                                            <select class="form-control" id="package" name="package"
                                                aria-label="แพคเกจในการสอบ / Packages" required>
                                                <option value="">Please Select</option>
                                                @foreach ($form->packages as $package)
                                                    <option value="{{ $package->id }}">{{ $package->package }}</option>
                                                @endforeach
                                                {{-- <option value="Test + tutoring class: 1250 bath">Test +
                                                    tutoring class: 1250 bath</option> --}}

                                            </select>
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
                                </div>

                                <div class="form-row form-focus">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="address">{{ $form->student_address ?? ' ' }} <span
                                                    class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="address" id="address"
                                                maxlength="150" minlength="2" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Street Address</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label form-label-top form-label-auto" for="postcode">
                                                {{ $form->student_zip ?? ' ' }} <span class="form-required">*</span>
                                            </label>
                                            <input type="text" class="form-control" name="postcode" id="postcode"
                                                maxlength="50" minlength="2" required>
                                            <label class="form-sub-label" style="min-height:13px" aria-hidden="false">
                                                Postal / Zip Code / รหัสไปรษณีย์ </label>
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
                                    <div class="col-md-6">
                                        <div class="position-relative form-group" style="padding-top:24px;">
                                            <label class="form-label form-label-top form-label-auto" for="parent">
                                                {{ $form->parent_name ?? ' ' }} <span
                                                    class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="parent" id="parent"
                                                value="" maxlength="100" required>
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
                                    <div class="col-md-6 col-sm-12">
                                        <div class="position-relative form-group" style="padding-top:24px;">
                                            <label class="form-label form-label-top form-label-auto" for="phone">
                                                {{ $form->parent_phone ?? ' ' }}
                                                <span class="form-required">*</span>
                                                <div style="width: 270px"></div>
                                            </label>
                                            <input type="text" class="AddPhone form-control" name="phone"
                                                id="phone" value="" maxlength="20" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Phone Number</label>
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
                                    <div class="col-md-6 col-sm-12">
                                        <div class="position-relative form-group" style="padding-top:0px;">
                                            <label class="form-label form-label-top form-label-auto" for="grade">
                                                {{ $form->contact_email ?? ' ' }}
                                                {{ $form->contact_email_detail ?? ' ' }}
                                                <span class="form-required">*</span></label>
                                            <input type="email" class="form-control" name="email" id="email"
                                                value="" maxlength="70" required>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">example@example.com</label>
                                            <div class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10"> <span class="error-navigation-message">Please input
                                                    correct email.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-focus" id="dropzone-photo">
                                    <div class="col-md-12 col-sm-12">
                                        <a id="dphoto"></a>
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="photo">
                                                {{ $form->student_photo ?? ' ' }} <span class="form-required">*</span>
                                                <div style="width: 280px"></div>
                                            </label>
                                            <input type="text" id="drop1" name="drop1"
                                                class="form-control form_none" value="" required>
                                            <div class="dropzone" id="my-awesome-dropzone" style="font-size: 1.5em;">
                                                <div class="fallback">

                                                </div>
                                            </div>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Photo ขนาดไม่เกิน 5mb</label>
                                            <div id="dropzone-photo-error" class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10">
                                                <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropzonex dz-default dz-message" id="dropzone_preview"
                                    style="font-size: 1.5em;">
                                    <h3 class="dropzone-previews ui"></h3>


                                </div>

                                <br>
                                <div align="center">
                                    @if (isset($form->image_payment))
                                        <img src="{{ asset('form_manage/payment/' . $form->image_payment) }}"
                                            alt="..." height="500">
                                    @endif
                                </div>
                                <br>

                                <div class="form-row form-focus" id="dropzone-att">
                                    <div class="col-md-12 col-sm-12">
                                        <a id="datt"></a>
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="att">
                                                {{ $form->student_slip_photo ?? ' ' }} <span
                                                    class="form-required">*</span>
                                                <div style="width: 280px"></div>
                                            </label>
                                            <input type="text" id="drop2" name="drop2"
                                                class="form-control form_none" value="" required>
                                            <div class="dropzone" id="my-awesome-dropzone2" style="font-size: 1.5em;">
                                                <div class="fallback">
                                                    {{--  <input name="file2" id="file2" type="file" multiple
                                                        class="form-control" /> --}}
                                                </div>
                                            </div>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">Receipt Photo ขนาดไม่เกิน 5mb</label>
                                            <div id="dropzone-att-error" class="form-error-message" role="alert"><img
                                                    src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                    height="10">
                                                <span class="error-navigation-message">This field is
                                                    required.</span>
                                                <div class="form-error-arrow">
                                                    <div class="form-error-arrow-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropzonex dz-default dz-message" id="dropzone_preview2"
                                    style="font-size: 1.5em;">
                                    <h3 class="dropzone-previews ui"></h3>
                                </div>
                                <div class="form-row form-focus">
                                    <div class="col-md-6 ">
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto" for="newsf">
                                                {{ $form->news ?? ' ' }} <span class="form-required">*</span></label>
                                            <input type="text" class="form-control" name="newsf" id="newsf"
                                                value="" maxlength="50" required>

                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false"></label>
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
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <br>
                            <div class="text-danger">
                                <h3>
                                    {!! nl2br($form->text_warning) ?? '' !!}
                                </h3>
                            </div>
                            <br>
                            <div class="text-danger">
                                {!! nl2br($form->text_footer) ?? '' !!}

                            </div>
                            <br>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="agreeCheckbox" required
                                    style="float: none; margin-right: 3px;">
                                <label class="form-check-label" for="agreeCheckbox">
                                    {!! nl2br($form->text_policy) ?? '' !!}
                                </label>
                            </div>

                            <button type="submit" class="submit-btn btn btn-lg btn-primary">Submit Application</button>
                            <button type="reset" class="btn btn-lg btn-primary">Clear Form</button><br><br><br>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var inputlist = ["firstname", "lastname", "address", "postcode"];
            $(".form-control").focusout(function() {
                //$(".form-control").focus();
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

                } else if (nameinput == 'address') {
                    if ($(this).val() == '' || $('#postcode').val() == '') {
                        //alert();
                        $(this).closest(inputxx).addClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                        if ($(this).val() == '') {
                            $(this).css('border', 'solid 1px red');
                        } else {
                            $(this).css('border', 'solid 1px green');
                        }

                        if ($("#postcode").val() == '') {
                            $("#postcode").css('border', 'solid 1px red');
                        } else {
                            $("#postcode").css('border', 'solid 1px green');
                        }
                        //$(this).parents().css( "background-color", "red" );
                    } else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $("#postcode").css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }

                } else if (nameinput == 'postcode') {
                    if ($(this).val() == '' || $('#address').val() == '') {
                        //alert();
                        $(this).closest(inputxx).addClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                        if ($(this).val() == '') {
                            $(this).css('border', 'solid 1px red');
                        } else {
                            $(this).css('border', 'solid 1px green');
                        }

                        if ($("#address").val() == '') {
                            $("#address").css('border', 'solid 1px red');
                        } else {
                            $("#address").css('border', 'solid 1px green');
                        }
                        //$(this).parents().css( "background-color", "red" );
                    } else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $("#address").css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }

                } else {
                    if ($(this).attr('type') == 'email') {
                        var validRegex =
                            /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
                        if ($(this).val() == '' || !$(this).val().match(validRegex)) {
                            $(this).closest(inputxx).addClass('form-focus-error');
                            $(this).closest(inputxx)
                                .find('.form-error-message').css("display", "block");
                            $(this).css('border', 'solid 1px red');
                            //$(this).parents().css( "background-color", "red" );
                        } else {
                            // If it is not blank.
                            $(this).css('border', 'solid 1px green');
                            $(this).closest(inputxx).removeClass('form-focus-error');
                            $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                        }

                    } else {
                        if ($(this).val() == '') {
                            $(this).closest(inputxx).addClass('form-focus-error');
                            $(this).closest(inputxx)
                                .find('.form-error-message').css("display", "block");
                            $(this).css('border', 'solid 1px red');
                            //$(this).parents().css( "background-color", "red" );
                        } else {
                            // If it is not blank.
                            $(this).css('border', 'solid 1px green');
                            $(this).closest(inputxx).removeClass('form-focus-error');
                            $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                        }
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

            $('#infinite').on('submit', function() {
                $('#loader-container').show();
            });

        });
    </script>
    <script type="text/javascript">
        var myDropzone = {};
        let imgFiles = document.getElementsByName('imgFiles[]');
        let imgFiles2 = document.getElementsByName('imgFiles2[]');

        Dropzone.options.myAwesomeDropzone = {
            url: '{{ route('form.upload') }}',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
            autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
            uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
            parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
            maxFiles: 2, // ไฟล์สูงสุด 5 ไฟล์
            maxfilesexceeded: function(file) {
                this.removeAllFiles();
                this.addFile(file);
            },
            addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
            maxFilesize: 5, // MB
            previewsContainer: "#dropzone_preview", // ระบุ element เป้าหลาย
            //previewTemplate: $('#template-preview').html(),
            dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
            renameFile: function(file) {
                let newName = new Date().getTime() + '_' + file.name;
                file.newName = newName;
                return newName;
            },
            dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
            dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Browse File</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
            dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
            acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้

            // The setting up of the dropzone
            init: function() {
                myDropzone = this;
                this.on("addedfile", function(file) {
                    //$('#infinite').append("<input type='text' class='form_none' name='imgFiles[]' value='" + file.newName + "'/>");
                    $('#infinite').append("<input type='text' id='" + file.newName +
                        "' class='form_none' name='imgFiles[]' value='" + file.newName + "'/>");
                    file.previewElement.id = file.newName;
                }).on("removedfile", function(file) {
                    //alert(file.id);
                    //var name = file.name;
                    var name = file.previewElement.id;

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        type: 'POST',
                        url: '{{ route('form.delete') }}',
                        data: {
                            name: name,
                        },
                        success: function(data) {
                            console.log('success: ' + data);
                            var element = document.getElementById("infinite");
                            var child = document.getElementById(name);
                            element.removeChild(child);
                            if (!document.getElementsByName('imgFiles[]').length) {
                                $('#dropzone_preview').css("display", "none");
                                $('#dropzone-photo').addClass('form-focus-error');
                                $('#dropzone-photo')
                                    .find('.form-error-message').css("display", "block");
                                $('#dropzone-photo').css('border', 'solid 1px red');
                            }
                        }
                    });

                    //var _ref;
                    //return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;

                }).on("maxfilesexceeded", function(file, response) {
                    alert('allow maximum 2 file');
                    this.removeFile(file);
                }).on('complete', function(file) {
                    let val = file.accepted;
                    if (file.accepted == true) {
                        obj = JSON.parse(file.xhr.response);
                        //file.previewElement.id = obj.success;
                        console.log(obj.success);
                        //$('#infinite').append("<input type='text' id='" + obj.success +
                        //    "' class='form_none' name='imgFiles[]' value='" + obj.success + "'/>");
                    }
                    let val1 = file.name;
                    $('#dropzone_preview').css("display", "block");
                    $('#dropzone-photo').removeClass('form-focus-error');
                    $('#dropzone-photo')
                        .find('.form-error-message').css("display", "none");
                    $('#dropzone-photo').css('border', 'solid 1px green');
                    if (document.getElementsByName('imgFiles[]').length) {
                        //$('#error_div').css('display', 'none');
                    }
                }).on("success", function(file) {
                    //var responseText = file.id // or however you would point to your assigned file ID here;
                    //console.log(response); // console should show the ID you pointed to
                    // do stuff with file.id ...
                }).on("error", function(file) {
                    alert("ไม่อนุญาตให้อัพโหลดไฟล์เกิน 5 MB");
                    while (imgFiles.length > 0) {
                        imgFiles[0].parentNode.removeChild(imgFiles[0]);
                    }
                    this.removeFile(file);
                    // alert(dictFileTooBig);
                });
                /*this.on("addedfile", function(file) {

                }).on("removedfile", function(file) {

                }).on("thumbnail", function(file) {

                }).on("error", function(file) {

                }).on("processing", function(file) {

                }).on("uploadprogress", function(file) {

                });*/

            }
        }

        Dropzone.options.myAwesomeDropzone2 = {
            url: '{{ route('form.upload') }}',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
            autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
            uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
            parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
            maxFiles: 1, // ไฟล์สูงสุด 5 ไฟล์
            maxfilesexceeded: function(file) {
                this.removeAllFiles();
                this.addFile(file);
            },
            addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
            maxFilesize: 5, // MB
            renameFile: function(file) {
                let newName = new Date().getTime() + '_' + file.name;
                file.newName = newName;
                return newName;
            },
            previewsContainer: "#dropzone_preview2", // ระบุ element เป้าหลาย
            //previewTemplate: $('#template-preview').html(),
            dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
            dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
            dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Browse File</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
            dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
            acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้
            // The setting up of the dropzone
            init: function() {
                myDropzone = this;
                this.on("addedfile", function(file) {
                    //$('#infinite').append("<input type='text' class='form_none' name='imgFiles[]' value='" + file.name + "'/>");
                    $('#infinite').append("<input type='text' id='" + file.newName +
                        "' class='form_none' name='imgFiles2[]' value='" + file.newName + "'/>");
                    file.previewElement.id = file.newName;
                }).on("removedfile", function(file) {
                    //alert(file.id);
                    //var name = file.name;
                    var name = file.previewElement.id;

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        type: 'POST',
                        url: '{{ route('form.delete') }}',
                        data: {
                            name: name,
                        },
                        success: function(data) {
                            console.log('success: ' + data);
                            var element = document.getElementById("infinite");
                            var child = document.getElementById(name);
                            element.removeChild(child);
                            if (!document.getElementsByName('imgFiles2[]').length) {
                                //alert();
                                $('#dropzone_preview2').css("display", "none");
                                $('#dropzone-att').addClass('form-focus-error');
                                $('#dropzone-att')
                                    .find('.form-error-message').css("display", "block");
                                $('#dropzone-att').css('border', 'solid 1px red');
                            }
                        }
                    });

                    //var _ref;
                    //return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;

                }).on("maxfilesexceeded", function(file) {
                    alert('allow maximum 2 file');
                    this.removeFile(file);
                }).on('complete', function(file) {
                    let val = file.accepted;
                    if (file.accepted == true) {
                        obj = JSON.parse(file.xhr.response);
                        //file.previewElement.id = obj.success;
                        console.log(obj.success);
                        //alert(obj.success);
                        //$('#infinite').append("<input type='text' id='" + obj.success +
                        //   "' class='form_none' name='imgFiles2[]' value='" + obj.success + "'/>");
                    }
                    let val1 = file.name;
                    $('#dropzone_preview2').css("display", "block");
                    $('#dropzone-att').removeClass('form-focus-error');
                    $('#dropzone-att')
                        .find('.form-error-message').css("display", "none");
                    $('#dropzone-att').css('border', 'solid 1px green');
                    if (document.getElementsByName('imgFiles2[]').length) {
                        //$('#error_div').css('display', 'none');
                    }
                }).on("success", function(file) {
                    //var responseText = file.id // or however you would point to your assigned file ID here;
                    //console.log(response); // console should show the ID you pointed to
                    // do stuff with file.id ...
                }).on("error", function(file) {
                    this.removeFile(file);
                    // this.removeFile(file);
                    console.log(file);
                    $('#imgFiles2').val('');
                    while (imgFiles2.length > 0) {
                        imgFiles2[0].parentNode.removeChild(imgFiles2[0]);
                    }
                    // document.getElementsByName('imgFiles2[]').length = 0;
                    alert("ไม่อนุญาตให้อัพโหลดไฟล์เกิน 5 MB");
                });
                /*this.on("addedfile", function(file) {

                }).on("removedfile", function(file) {

                }).on("thumbnail", function(file) {

                }).on("error", function(file) {

                }).on("processing", function(file) {

                }).on("uploadprogress", function(file) {

                });*/

            }
        }

        const valid_option = {
            onkeyup: false,
            showErrors: function(errorMap, errorList) {
                var errors = this.numberOfInvalids();
                /* if (!document.getElementsByName('imgFiles[]').length) {
                    errors = errors + 1;
                }
                if (!document.getElementsByName('imgFiles2[]').length) {
                    errors = errors + 1;
                } */
                if (errors) {
                    var message = errors == 1 ?
                        '<br>You missed <span class="error-rounded-bg">1</span> field. It has been highlighted' :
                        '<br>You have missed <span class="error-rounded-bg">' + errors +
                        '</span> fields. Please fill before submitted.';
                    $("#error_div").html(message);
                    $(".error_div").show();
                    $('#loader-container').hide();
                } else {
                    $(".error_div").hide();
                    $('#loader-container').hide();
                }
                this.defaultShowErrors();

            },

            submitHandler: function(form) {
                $('#loader-container').show();

                $('#send_data').attr('disabled', true);

                if (document.getElementsByName('imgFiles[]').length && document.getElementsByName('imgFiles2[]')
                    .length) {
                    $(form).submit();
                    return true;
                }

                $('#loader-container').hide();
                $('#send_data').attr('disabled', false);
                return false;
            }
        }

        $(document).ready(function() {
            // $('#loader-container').show();

            $(".AddDate").inputmask("99-99-9999");
            $(".AddPhone").inputmask("(999) 999-9999");

            $("#infinite").validate(valid_option);

            $('#btnUpload').on('click', function() {
                myDropzone.processQueue();
            });
        });

        function check_input() {
            $('.form-control').focus();
            //$('.form-control').focus({ preventScroll: false });
            //$('input').focus();
            //$('input').focus({ preventScroll: false });

            if (!document.getElementById('agreeCheckbox').checked) {
                alert(
                    'กรุณาติ๊ก "ตกลงและยอมรับข้อกำหนดและเงื่อนไข รวมถึงตรวจสอบข้อมูลและเนื้อหาต่างๆเรียบร้อยแล้ว" ก่อนส่งฟอร์ม'
                );
                return false;
            }

            if (document.getElementsByName('imgFiles[]').length && document.getElementsByName('imgFiles2[]')
                .length) {
                //$('#error_div').css('display', 'none');


                return true;
            } else {
                if (!document.getElementsByName('imgFiles[]').length) {
                    $('#dropzone-photo').addClass('form-focus-error');
                    $('#dropzone-photo')
                        .find('.form-error-message').css("display", "block");
                    $('#dropzone-photo').css('border', 'solid 1px red');
                    jumpTo('dphoto');
                }
                if (!document.getElementsByName('imgFiles2[]').length) {
                    $('#dropzone-att').addClass('form-focus-error');
                    $('#dropzone-att')
                        .find('.form-error-message').css("display", "block");
                    $('#dropzone-att').css('border', 'solid 1px red');
                    jumpTo('datt');
                }

                //$('#error_div').css('display', 'block');
                return false;
            }

        }

        function jumpTo(id) {
            var get = location.href;
            location.href = "#" + id;
        }

        var fixmeTop = $('.error_div').offset().top;
        $(window).scroll(function() {
            var currentScroll = $(window).scrollTop();
            if (currentScroll >= fixmeTop) {
                $('.error_div').css({
                    position: 'fixed',
                    top: '0',
                    left: '0'
                });
            } else {
                $('.error_div').css({
                    position: 'absolute'
                });
            }
        });
    </script>
    <script type="text/javascript">
        const warningTitleCSS = 'color:red; font-size:60px; font-weight: bold; -webkit-text-stroke: 1px black;';
        const warningDescCSS = 'font-size: 18px;';

        console.log('%cหยุด!', warningTitleCSS);
        console.log(
            "%cนี่เป็นคุณสมบัติการทำงานของเบราว์เซอร์ที่มีจุดมุ่งหมายให้ใช้สำหรับผู้พัฒนา หากผู้ใดบอกให้คุณคัดลอกแล้ววางข้อความบางอย่างที่นี่เพื่อเปิดใช้งานคุณสมบัติการทำงานของ ระบบ หรือเพื่อเข้าถึงบัญชีของบุคคลใดบุคคลหนึ่ง โดยมิชอบ คำบอกกล่าวเช่นนี้เป็นกลลวงและจะทำให้ผู้นั้นมีสิทธิ์เข้าถึงบัญชี ของคุณ",
            warningDescCSS);
        //console.log('%cSee https://www.facebook.com/selfxss for more information.', warningDescCSS);
    </script>
@endsection
