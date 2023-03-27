@extends('layouts.form')

@section('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            background-color: #d8e8b0;
        }

        .bg-light2 {
            background-color: #fff;
        }

        .card-header-custom {
            background-image: url("{{ asset('images/school_309241295.jpg') }}");
            height: 420px;
            background-repeat: no-repeat;
        }

        hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px solid #112d53;
        }

        .div_center {
            margin: auto;
            text-align: center;

        }

        .timeline {
            position: relative;
            padding: 21px 0px 10px;
            margin-top: 4px;
            margin-bottom: 30px;
        }

        .timeline .line {
            position: absolute;
            width: 4px;
            display: block;
            background: currentColor;
            top: 0px;
            bottom: 0px;
            margin-left: 30px;
        }

        .timeline .separator {
            border-top: 1px solid currentColor;
            padding: 5px;
            padding-left: 40px;
            font-style: italic;
            font-size: .9em;
            margin-left: 30px;
        }

        .timeline .line::before {
            top: -4px;
        }

        .timeline .line::after {
            bottom: -4px;
        }

        .timeline .line::before,
        .timeline .line::after {
            content: '';
            position: absolute;
            left: -4px;
            width: 12px;
            height: 12px;
            display: block;
            border-radius: 50%;
            background: currentColor;
        }

        .timeline .panel {
            position: relative;
            margin: 10px 0px 21px 70px;
            clear: both;
        }

        .timeline .panel::before {
            position: absolute;
            display: block;
            top: 8px;
            left: -24px;
            content: '';
            width: 0px;
            height: 0px;
            border: inherit;
            border-width: 12px;
            border-top-color: transparent;
            border-bottom-color: transparent;
            border-left-color: transparent;
        }

        .timeline .panel .panel-heading.icon * {
            font-size: 20px;
            vertical-align: middle;
            line-height: 40px;
        }

        .timeline .panel .panel-heading.icon {
            position: absolute;
            left: -59px;
            display: block;
            width: 40px;
            height: 40px;
            padding: 0px;
            border-radius: 50%;
            text-align: center;
            float: left;
        }

        .timeline .panel-outline {
            border-color: transparent;
            background: transparent;
            box-shadow: none;
        }

        .timeline .panel-outline .panel-body {
            padding: 10px 0px;
        }

        .timeline .panel-outline .panel-heading:not(.icon),
        .timeline .panel-outline .panel-footer {
            display: none;
        }
    </style>
@endsection


@section('content')
    <div id="loader"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card bg-light2 mb-3 ">
                    <div class="card-header card-header-custom" style="background-color: #fff;">
                        <div class="div_center avatar mx-auto white">
                            <img class="rounded-circle img-responsive bg-white p-3 image"
                                style="margin-top: 300px; border:solid #eeeded 1px ;"
                                src="{{ asset('images/infinite.jpg') }}" alt="..." height="200">
                        </div>
                    </div>


                    <div class="card-body">
                        <br>
                        <div class="text-center">
                            <br><br><br>
                            <h3 class="card-title">Student Information</h3>
                        </div>
                        <br>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="card card-primary card-outline card-tabs">
                                    <div class="card-header p-0 pt-1 border-bottom-0">
                                        <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">

                                            <li class="nav-item">
                                                <a class="nav-link active" id="custom-tabs-three-profile-tab"
                                                    data-toggle="pill" href="#custom-tabs-three-profile" role="tab"
                                                    aria-controls="custom-tabs-three-profile"
                                                    aria-selected="false">ข้อมูลนักเรียน</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="custom-tabs-three-messages-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-messages" role="tab"
                                                    aria-controls="custom-tabs-three-messages"
                                                    aria-selected="false">รายวิชาที่เรียน / สถานะการเรียน</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="custom-tabs-three-settings-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-settings" role="tab"
                                                    aria-controls="custom-tabs-three-settings" aria-selected="false">Comment
                                                    อาจารย์</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link " id="custom-tabs-three-home-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-home" role="tab"
                                                    aria-controls="custom-tabs-three-home"
                                                    aria-selected="true">ข้อมูลผู้ปกครอง</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link " id="custom-tabs-three-promotion-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-promotion" role="tab"
                                                    aria-controls="custom-tabs-three-promotion"
                                                    aria-selected="true">โปรโมชั่น</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link " id="custom-tabs-three-holiday-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-holiday" role="tab"
                                                    aria-controls="custom-tabs-three-holiday"
                                                    aria-selected="true">วันหยุดโรงเรียน</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content" id="custom-tabs-three-tabContent">
                                            <div class="tab-pane fade " id="custom-tabs-three-home" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-home-tab">
                                                {!! $parent_html !!}
                                            </div>
                                            <div class="tab-pane fade show active" id="custom-tabs-three-profile"
                                                role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab">
                                                {!! $student_html !!}
                                                <div class="container rounded bg-white mt-5 mb-5">
                                                    <div class="row">
                                                        <div class="col-md-4 border-right">
                                                            <div
                                                                class="d-flex flex-column align-items-center text-center p-3 py-5">
                                                                <img class="rounded-circle mt-5" width="150px"
                                                                    src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg"><span
                                                                    class="font-weight-bold">Edogaru</span><span
                                                                    class="text-black-50">edogaru@mail.com.my</span><span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7 border-right">
                                                            <div class="p-3 py-5">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h4 class="text-right">Profile Settings</h4>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-6"><label
                                                                            class="labels">Name</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="first name" value=""></div>
                                                                    <div class="col-md-6"><label
                                                                            class="labels">Surname</label><input
                                                                            type="text" class="form-control"
                                                                            value="" placeholder="surname"></div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-12"><label class="labels">Mobile
                                                                            Number</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter phone number"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label class="labels">Address
                                                                            Line 1</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter address line 1"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label class="labels">Address
                                                                            Line 2</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">Postcode</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">State</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">Area</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label class="labels">Email
                                                                            ID</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter email id" value="">
                                                                    </div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">Education</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="education" value=""></div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6"><label
                                                                            class="labels">Country</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="country" value=""></div>
                                                                    <div class="col-md-6"><label
                                                                            class="labels">State/Region</label><input
                                                                            type="text" class="form-control"
                                                                            value="" placeholder="state"></div>
                                                                </div>
                                                                <div class="mt-5 text-center"><button
                                                                        class="btn btn-primary profile-button"
                                                                        type="button">Save Profile</button></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="container rounded bg-white mt-5 mb-5">
                                                    <div class="row">
                                                        <div class="col-md-4 border-right">
                                                            <div
                                                                class="d-flex flex-column align-items-center text-center p-3 py-5">
                                                                <img class="rounded-circle mt-5" width="150px"
                                                                    src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg"><span
                                                                    class="font-weight-bold">Edogaru</span><span
                                                                    class="text-black-50">edogaru@mail.com.my</span><span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7 border-right">
                                                            <div class="p-3 py-5">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h4 class="text-right">Profile Settings</h4>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-6"><label
                                                                            class="labels">Name</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="first name" value=""></div>
                                                                    <div class="col-md-6"><label
                                                                            class="labels">Surname</label><input
                                                                            type="text" class="form-control"
                                                                            value="" placeholder="surname"></div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-12"><label class="labels">Mobile
                                                                            Number</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter phone number"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label class="labels">Address
                                                                            Line 1</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter address line 1"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label class="labels">Address
                                                                            Line 2</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">Postcode</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">State</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">Area</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="enter address line 2"
                                                                            value=""></div>
                                                                    <div class="col-md-12"><label class="labels">Email
                                                                            ID</label><input type="text"
                                                                            class="form-control"
                                                                            placeholder="enter email id" value="">
                                                                    </div>
                                                                    <div class="col-md-12"><label
                                                                            class="labels">Education</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="education" value=""></div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6"><label
                                                                            class="labels">Country</label><input
                                                                            type="text" class="form-control"
                                                                            placeholder="country" value=""></div>
                                                                    <div class="col-md-6"><label
                                                                            class="labels">State/Region</label><input
                                                                            type="text" class="form-control"
                                                                            value="" placeholder="state"></div>
                                                                </div>
                                                                <div class="mt-5 text-center"><button
                                                                        class="btn btn-primary profile-button"
                                                                        type="button">Save Profile</button></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="custom-tabs-three-messages" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-messages-tab">
                                                <div style="float: right; padding-right: 250px;">
                                                    <b> Student Code: </b> ( 009 ) <b>Name:</b>
                                                    (Pakin)
                                                    <b>School Grade:</b> ( K3 ) <b>
                                                        Book Start:</b> ( 22 )
                                                </div>
                                                <table style="width: 100%;" id="Listview"
                                                    class="table table-striped table-bordered responsive-utilities jambo_table bulk_action">
                                                    <thead>
                                                        <tr>
                                                            <th>School Grade</th>
                                                            <th class="text-center">Level</th>
                                                            <th colspan="12" class="text-center">( K3 )</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <tr>
                                                            <td width="35%">
                                                                <font size="2">Level I Verb sentences (past, present,
                                                                    future,) nouns (irregular, abstract noun,) spelling
                                                                    (e.g., commonly misspelled words, spoken and formal
                                                                    English,) text structures (e.g., identifying text
                                                                    structure, explanatory text,) story structure (e.g.,
                                                                    plot p, character, resolution,) literary devices (e.g.,
                                                                    simile, metaphor, onomatopoeia,) writing</font>
                                                            </td>
                                                            <td width="100" align="center">I</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="35%">
                                                                <font size="2">Level H Reading of Text features,
                                                                    features of informational text, research, word work
                                                                    (e.g., Greek and Latin roots, clarifying meaning,)
                                                                    synonyms and antonyms, sentences, sentences, conventions
                                                                    (possessive, quotation marks, articles,) comprehension
                                                                    strategies, writing</font>
                                                            </td>
                                                            <td width="100" align="center">H</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td align="right"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 50%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="35%">
                                                                <font size="2">Level G Part of speech, abbreviation,
                                                                    reading comprehension of fact and opinion, fiction
                                                                    reading, informational text, narrative text, spelling
                                                                    (root words, prefixes, suffixes, homophones,) writing
                                                                </font>
                                                            </td>
                                                            <td width="100" align="center">G</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td align="right"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 25%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 50%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="35%">
                                                                <font size="2">Level F 3-letter blends, consonant
                                                                    clusters, silent letters, long vowel spellings,
                                                                    consonant digraphs, soft c and g sound, prefixes,
                                                                    suffixes, part of speech, adverbs, subjects and
                                                                    predicates, objects, subject, object, and reflexive
                                                                    pronouns, contraction, possessive, commas, writing
                                                                </font>
                                                            </td>
                                                            <td width="100" align="center">F</td>
                                                            <td align="right"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 0%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 75%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="35%">
                                                                <font size="2">Level E R-controlled vowels, vowel
                                                                    teams (e.g.ee, ou, aw,) vowel diphthongs, inflectional
                                                                    ending (-ed,) Y as a vowel, multi syllabic words,
                                                                    question words, verbs (irregular past tense, future
                                                                    tense,) capitalization, writing questions, paragraph
                                                                    writing</font>
                                                            </td>
                                                            <td width="100" align="center">E</td>
                                                            <td align="left"
                                                                style="padding:0; margin: 0; margin-right: 0px; margin-left: 0px; "
                                                                width="80">
                                                                <div class="progress-bar-xs mb-3 progress"
                                                                    style="width: 100%;">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        aria-valuenow="100" aria-valuemin="0"
                                                                        aria-valuemax="100" style="width: 100%;"></div>
                                                                </div>
                                                            </td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                            <td width="80"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <font size="2">Level D Consonant digraph (beginning
                                                                    and ending,) consonant blend (ending,) silent e, Y as
                                                                    vowel, adjectives, nouns, verbs (irregular present tense
                                                                    and past tense,) subject-verb agreement (present tense),
                                                                    short sentences writing</font>
                                                            </td>
                                                            <td align="center">D</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <font size="2">Level C Phonics (consonant ending
                                                                    sounds,) word families, double consonants, nouns, verbs,
                                                                    sentences, text reading, reading comprehension, short
                                                                    sentences writing</font>
                                                            </td>
                                                            <td align="center">C</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <font size="2">Level B Phonics (consonant beginning
                                                                    sounds and short vowels,) basic words, text reading,
                                                                    reading comprehension, coloring mini-book</font>
                                                            </td>
                                                            <td align="center">B</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <font size="2">Level A Alphabet writing: capital and
                                                                    lowercase, basic words, alphabet practice sheet</font>
                                                            </td>
                                                            <td align="center">A</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <font size="2">Level Pre-A Colors, shapes, motor
                                                                    skills, basic nouns, orientation, words (e.g., rhyming
                                                                    word, book awareness), no writing</font>
                                                            </td>
                                                            <td align="center">Pre - A</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>


                                                    </tbody>

                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="2" align="center">Year</td>
                                                            <td align="center" colspan="12">2023</td>

                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" align="center">Month</td>

                                                            <td align="center">1</td>
                                                            <td align="center">2</td>
                                                            <td align="center">3</td>
                                                            <td align="center">4</td>
                                                            <td align="center">5</td>
                                                            <td align="center">6</td>
                                                            <td align="center">7</td>
                                                            <td align="center">8</td>
                                                            <td align="center">9</td>
                                                            <td align="center">10</td>
                                                            <td align="center">11</td>
                                                            <td align="center">12</td>
                                                        </tr>
                                                    </tfoot>

                                                </table>


                                            </div>
                                            <div class="tab-pane fade" id="custom-tabs-three-settings" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-settings-tab">
                                                <div class="timeline">

                                                    <div class="time-label">
                                                        <span class="bg-red">10 Feb. 2014</span>
                                                    </div>


                                                    <div>
                                                        <i class="fas fa-envelope bg-blue"></i>
                                                        <div class="timeline-item">
                                                            <span class="time"><i class="fas fa-clock"></i> 12:05</span>
                                                            <h3 class="timeline-header"><a href="#">Support Team</a>
                                                                sent you an email</h3>
                                                            <div class="timeline-body">
                                                                Etsy doostang zoodles disqus groupon greplin oooj voxy
                                                                zoodles,
                                                                weebly ning heekya handango imeem plugg dopplr jibjab,
                                                                movity
                                                                jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo
                                                                kaboodle
                                                                quora plaxo ideeli hulu weebly balihoo...
                                                            </div>
                                                            <div class="timeline-footer">
                                                                <a class="btn btn-primary btn-sm">Read more</a>
                                                                <a class="btn btn-danger btn-sm">Delete</a>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div>
                                                        <i class="fas fa-user bg-green"></i>
                                                        <div class="timeline-item">
                                                            <span class="time"><i class="fas fa-clock"></i> 5 mins
                                                                ago</span>
                                                            <h3 class="timeline-header no-border"><a href="#">Sarah
                                                                    Young</a> accepted your friend request</h3>
                                                        </div>
                                                    </div>


                                                    <div>
                                                        <i class="fas fa-comments bg-yellow"></i>
                                                        <div class="timeline-item">
                                                            <span class="time"><i class="fas fa-clock"></i> 27 mins
                                                                ago</span>
                                                            <h3 class="timeline-header"><a href="#">Jay White</a>
                                                                commented on your post</h3>
                                                            <div class="timeline-body">
                                                                Take me to your leader!
                                                                Switzerland is small and neutral!
                                                                We are more like Germany, ambitious and misunderstood!
                                                            </div>
                                                            <div class="timeline-footer">
                                                                <a class="btn btn-warning btn-sm">View comment</a>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="time-label">
                                                        <span class="bg-green">3 Jan. 2014</span>
                                                    </div>


                                                    <div>
                                                        <i class="fa fa-camera bg-purple"></i>
                                                        <div class="timeline-item">
                                                            <span class="time"><i class="fas fa-clock"></i> 2 days
                                                                ago</span>
                                                            <h3 class="timeline-header"><a href="#">Mina Lee</a>
                                                                uploaded new photos</h3>
                                                            <div class="timeline-body">
                                                                <img src="https://placehold.it/150x100" alt="...">
                                                                <img src="https://placehold.it/150x100" alt="...">
                                                                <img src="https://placehold.it/150x100" alt="...">
                                                                <img src="https://placehold.it/150x100" alt="...">
                                                                <img src="https://placehold.it/150x100" alt="...">
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div>
                                                        <i class="fas fa-video bg-maroon"></i>
                                                        <div class="timeline-item">
                                                            <span class="time"><i class="fas fa-clock"></i> 5 days
                                                                ago</span>
                                                            <h3 class="timeline-header"><a href="#">Mr. Doe</a>
                                                                shared a video</h3>
                                                            <div class="timeline-body">
                                                                <div class="embed-responsive embed-responsive-16by9">
                                                                    <iframe class="embed-responsive-item"
                                                                        src="https://www.youtube.com/embed/tMWkeBIohBs"
                                                                        allowfullscreen></iframe>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-footer">
                                                                <a href="#" class="btn btn-sm bg-maroon">See
                                                                    comments</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade " id="custom-tabs-three-promotion" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-promotion-tab">
                                            </div>
                                            <div class="tab-pane fade " id="custom-tabs-three-holiday" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-holiday-tab">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="card-footer text-center" style="background-color: #fff">
                                Copyright © 2022 infinitebrain.co.th All rights reserved.
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $(".nav-tabs a").click(function() {
                        $(this).tab('show');
                    });
                });
            </script>
        @endsection
