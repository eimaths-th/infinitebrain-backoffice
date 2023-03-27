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
                            <br>
                            <br>
                            <br>
                            <h3 class="card-title form_header">Student Information</h3>
                        </div>
                        <br>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="card card-primary card-outline card-tabs">
                                    <div class="card-header p-0 pt-1 border-bottom-0">
                                        <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="custom-tabs-three-home-tab"
                                                    data-toggle="pill" href="#custom-tabs-three-home" role="tab"
                                                    aria-controls="custom-tabs-three-home"
                                                    aria-selected="true">ข้อมูลผู้ปกครอง</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-profile" role="tab"
                                                    aria-controls="custom-tabs-three-profile"
                                                    aria-selected="false">ข้อมูลนักเรียน</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="custom-tabs-three-messages-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-messages" role="tab"
                                                    aria-controls="custom-tabs-three-messages"
                                                    aria-selected="false">รายวิชาที่เรียน</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="custom-tabs-three-settings-tab" data-toggle="pill"
                                                    href="#custom-tabs-three-settings" role="tab"
                                                    aria-controls="custom-tabs-three-settings" aria-selected="false">Comment
                                                    อาจารย์</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content" id="custom-tabs-three-tabContent">
                                            <div class="tab-pane fade show active" id="custom-tabs-three-home"
                                                role="tabpanel" aria-labelledby="custom-tabs-three-home-tab">
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada
                                                lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam
                                                ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor
                                                felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi,
                                                vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique
                                                senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu
                                                lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim
                                                sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin
                                                porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus
                                                pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum
                                                at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                                            </div>
                                            <div class="tab-pane fade" id="custom-tabs-three-profile" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-profile-tab">
                                                Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris
                                                pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum
                                                dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in
                                                faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas
                                                sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere
                                                purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at,
                                                posuere nec nunc. Nunc euismod pellentesque diam.
                                            </div>
                                            <div class="tab-pane fade" id="custom-tabs-three-messages" role="tabpanel"
                                                aria-labelledby="custom-tabs-three-messages-tab">
                                                Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris.
                                                Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa
                                                eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer
                                                vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit
                                                condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis
                                                velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum
                                                odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla
                                                lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum
                                                metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac
                                                ornare magna.
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
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="card-footer text-center" style="background-color: #fff">

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
