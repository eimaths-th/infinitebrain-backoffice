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
    <script defer src="{{ asset('architectui/assets/scripts/main.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/demo.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/toastr.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/scrollbar.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/fullcalendar.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/maps.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/chart_js.js') }}"></script>

      <!-- Scripts 
      @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/infinite.css') }}">
</head>

<body>
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">

        <!-- Header -->
        @include('layouts.header')

        <!-- Theme Setting -->
        @include('layouts.theme_setting')

       
        <div class="app-main">


            <!-- Sidebar -->
            @include('layouts.sidebar')

            <div class="app-main__outer">
                <div class="app-main__inner">
                    <!-- Your Page Content Here -->
                    @yield('content')
                </div>
                <!-- Footer -->
                @include('layouts.footer')
            </div>
            <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>



</html>
