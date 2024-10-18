<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>อินฟินิท เบรน รับสอนพิเศษ ติวเตอร์ กวดวิชา และให้บริการต่างๆ ที่เกี่ยวกับการศึกษาชั้นนำของประเทศ </title>
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="This is an example dashboard created using build-in elements and components.">
    <meta name="msapplication-tap-highlight" content="no">

    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
    <!--
    =========================================================
    * ArchitectUI HTML Theme Dashboard - v1.0.0
    =========================================================
    * Product Page: https://dashboardpack.com
    * Copyright 2019 DashboardPack (https://dashboardpack.com)
    * Licensed under MIT (https://github.com/DashboardPack/architectui-html-theme-free/blob/master/LICENSE)
    =========================================================
    * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
    -->
    <link rel="icon" type="image/png" href="../images/inf-icon2.png">


    <script defer src="{{ asset('architectui/assets/scripts/main.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    @yield('style')
    <script defer src="{{ asset('architectui/assets/scripts/demo.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/toastr.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/scrollbar.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/fullcalendar.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/maps.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/chart_js.js') }}"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="/js/app.js?v=1"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script src="/assets/plugins/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Scripts
      @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    -->
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    <link href="../assets/css/app.minn.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/infinite.css') }}">

    <style>
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -5px;
            margin-left: -5px
        }

        .form-row>.col,
        .form-row>[class*=col-] {
            padding-right: 5px;
            padding-left: 5px
        }

        .example {
            width: 50%;
            /* 50% of the parent container's width */
            font-size: 2em;
            /* 2 times the default font size */
        }

        /* @media screen and (max-width: 400px) {
            .screen-auto {
                max-width: 100%;
                height: auto;
            }
        }

        .screen-auto {
            max-width: 800;
            height: 200;
        } */
    </style>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="../assets/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
    <style>
        @media screen and (max-width: 100px) {
            .screen-auto {
                max-width: 100%;
                height: auto;
            }
        }

        .screen-auto {
            max-width: 800;
            height: 200;
        }

        .line-contact-btn {
            position: fixed;
            bottom: 100px;
            right: 20px;
            background-color: #00B900;
            /* Line's green color */
            color: white;
            padding: 15px 20px;
            border-radius: 50px;
            text-align: center;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
    </style>
    {{-- <link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="../assets/plugins/select2/dist/js/select2.min.js"></script> --}}
</head>

<body>

    @php
        // dd(request('ref'));
        $ref = 'uaLPxKcg9Y';
        $url = url()->full();
        session(['ref' => $ref]);
    @endphp

    {{-- <a href="https://line.me/R/ti/p/@352vgbrf?ref={{ $ref }}" target="_blank" class="line-contact-btn">

        Contact Admin on Line OA
    </a> --}}

    <a href="https://line.me/R/ti/p/@352vgbrf?ref={{ $ref }}" target="_blank" class="line-contact-btn">
        Contact Admin on Line OA
    </a>
    {{-- <a href="{{ route('send.line.message', ['ref' => $ref, 'url' => $url]) }}" target="_blank"
        class="line-contact-btn">
        Contact Admin on Line OA
    </a> --}}
</body>

</html>
