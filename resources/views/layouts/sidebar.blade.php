@php
    use App\Models\Subject;
    use App\Models\SubjectPrice;

@endphp
<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ms-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                {{-- <li class="app-sidebar__heading">Dashboards</li>
                <li>
                    <a href="{{ route('users') }}" class="mm-active">
                        <i class="metismenu-icon pe-7s-rocket"></i>
                        Dashboard
                    </a>
                </li> --}}


                @if (Auth::user()->level == 2 || Auth::user()->level == 1 || Auth::user()->level == 0)


                    <li class="app-sidebar__heading">  </li>
                    <li>
                        <a href="{{ route('home') }}">
                            <i class="metismenu-icon pe-7s-home"></i>
                            Dashboard
                            {{-- <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i> --}}
                        </a>
                    </li>
                    <li class="app-sidebar__heading">โรงเรียน </li>

                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-users"></i>
                            ผู้ปกครอง / นักเรียน
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                            <li>
                                <a href="{{ route('students') }}">
                                    <i class="metismenu-icon"></i>
                                    รายชื่อนักเรียน
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('parents') }}">
                                    <i class="metismenu-icon"></i>
                                    รายชื่อผู้ปกครอง
                                </a>
                            </li>

                        </ul>

                    </li>

                    <li>
                        <a href="{{ route('receipt_school') }}">
                            <i class="metismenu-icon pe-7s-note2"></i>
                            Receipt (ค่าสมัครเรียน)
                        </a>

                        <!-- <ul>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '2']) }}">
                                    <i class="metismenu-icon"></i>
                                    GMEC
                                </a>
                            </li>
                        </ul> -->
                            </li>

                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-graph2"></i>
                            Student Progress
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>

                            <li>
                                <a href="{{ route('course') }}">
                                    <i class="metismenu-icon"></i>
                                    Progress Chart
                                </a>
                            </li>
                        </ul>

                    </li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-diskette"></i>
                            ประวัติการเรียน
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>

                            <li>
                                <a href="{{ route('histories') }}">
                                    <i class="metismenu-icon"></i>
                                    ประวัติการเข้าเรียน
                                </a>
                            </li>
                        </ul>

                    </li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-comment"></i>
                            Comment ครู
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>

                            <li>
                                <a href="{{ route('comment') }}">
                                    <i class="metismenu-icon"></i>
                                    Comment
                                </a>
                            </li>
                        </ul>

                    </li>

                            <li>
                                <a href="#">
                                    <i class="metismenu-icon pe-7s-study"></i>
                                    รายวิชา ( Subject )
                                    <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                </a>

                                <ul>
                                    <li>
                                        <a href="{{ route('subjects') }}">
                                            <i class="metismenu-icon"></i>
                                            รายวิชาที่เปิดสอน
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('subjects.overview') }}">
                                            <i class="metismenu-icon"></i>
                                            รายชื่อนักเรียนในรายวิชา
                                        </a>
                                    </li>


                                </ul>


                            </li>



                            <li>
                                <a href="#">
                                    <i class="metismenu-icon pe-7s-network"></i>
                                    ระดับ ( Level )
                                    <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                </a>


                                <ul>
                                    @php
                                        $data = Subject::orderBy("id", "asc")->get();
                                        $dataGroup = SubjectPrice::orderBy("id", "asc") ->get()->groupBy('subject_id');
                                    @endphp
                                    @foreach ($data as $key)
                                        <li>
                                            <a href="{{ route('levels', ['id' => $key->id]) }}">
                                                <i class="metismenu-icon"></i>
                                                {{ $key->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                    {{-- <li>
                                        <a href="{{ route('levels', ['id' => '1']) }}">
                                            <i class="metismenu-icon"></i>
                                            Math
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('levels', ['id' => '2']) }}">
                                            <i class="metismenu-icon"></i>
                                            Play Math
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('levels', ['id' => '3']) }}">
                                            <i class="metismenu-icon"></i>
                                            Private Math
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('levels', ['id' => '4']) }}">
                                            <i class="metismenu-icon"></i>
                                            English A-I
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('levels', ['id' => '5']) }}">
                                            <i class="metismenu-icon"></i>
                                            English 5-8
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('levels', ['id' => '6']) }}">
                                            <i class="metismenu-icon"></i>
                                            English Spark
                                        </a>
                                    </li> --}}
                                </ul>

                            </li>
                            <li>
                                <a href="#">
                                    <i class="metismenu-icon pe-7s-cash"></i>
                                      ฐานข้อมูลค่าเล่าเรียน
                                    <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                </a>

                                <ul>

                                    {{-- <li>
                                        <a href="{{ route('subjects_price', ['id' => '1']) }}">
                                            <i class="metismenu-icon"></i>
                                            Math
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('subjects_price', ['id' => '2']) }}">
                                            <i class="metismenu-icon"></i>
                                            Play Math
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('subjects_price', ['id' => '3']) }}">
                                            <i class="metismenu-icon"></i>
                                            Private Math
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('subjects_price', ['id' => '4']) }}">
                                            <i class="metismenu-icon"></i>
                                            English A-I
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('subjects_price', ['id' => '5']) }}">
                                            <i class="metismenu-icon"></i>
                                            English 5-8
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('subjects_price', ['id' => '6']) }}">
                                            <i class="metismenu-icon"></i>
                                            English Spark
                                        </a>
                                    </li> --}}
                                    @foreach ($data as $subjectt)
                                            <li>
                                                <a href="{{ route('subjects_price', ['id' => $subjectt->id]) }}">
                                                    <i class="metismenu-icon"></i>
                                                    {{ $subjectt->title }}
                                                </a>
                                            </li>

                                    @endforeach


                                </ul>


                            </li>

                @endif

                @if (Auth::user()->level == 3 || Auth::user()->level == 1 || Auth::user()->level == 0)
                    <li class="app-sidebar__heading">สนามสอบ</li>
                    <li>
                        <a href="{{ route('formsControl') }}">
                            <i class="metismenu-icon pe-7s-clock"></i>
                            SetTime

                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-note2"></i>
                            ข้อมูลจาก Online Form
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                            <!--<li>
                                <a href="{{ route('receipts') }}">
                                    <i class="metismenu-icon"></i>
                                    Tax invoice / Receipt
                                </a>
                            </li>-->
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '9']) }}">
                                    <i class="metismenu-icon"></i>
                                    OLPCS
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '10']) }}">
                                    <i class="metismenu-icon"></i>
                                    ASIAROPE
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '11']) }}">
                                    <i class="metismenu-icon"></i>
                                    Cambridge
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '2']) }}">
                                    <i class="metismenu-icon"></i>
                                    GMEC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '4']) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '13']) }}">
                                    <i class="metismenu-icon"></i>
                                    ISOCSEA
                                </a>
                            </li>
                             <li>
                                <a href="{{ route('receipts_form', ['sfield' => '14']) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA+ISOCSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '5']) }}">
                                    <i class="metismenu-icon"></i>
                                    INFINITE Spelling Bee
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '6']) }}">
                                    <i class="metismenu-icon"></i>
                                    GELOSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '7']) }}">
                                    <i class="metismenu-icon"></i>
                                    LIMOC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '8']) }}">
                                    <i class="metismenu-icon"></i>
                                    STEM
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '15']) }}">
                                    <i class="metismenu-icon"></i>
                                    Huaxiacup
                                </a>
                            </li>
                            {{-- <li>
                                <a href="{{ route('receipts_form', ['sfield' => '16']) }}">
                                    <i class="metismenu-icon"></i>
                                    Mathleague
                                    <!-- ml -->
                                </a>
                            </li> --}}

                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '0']) }}">
                                    <i class="metismenu-icon"></i>
                                    Img Reward
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => 'TT']) }}">
                                    <i class="metismenu-icon"></i>
                                    Test Field
                                </a>
                            </li>

                        </ul>
                    </li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-note2"></i>
                            Receipt (ค่าสมัครสอบ)
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                            <!--<li>
                                <a href="{{ route('receipts') }}">
                                    <i class="metismenu-icon"></i>
                                    Tax invoice / Receipt
                                </a>
                            </li>-->
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '9']) }}">
                                    <i class="metismenu-icon"></i>
                                    OLPCS
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '10']) }}">
                                    <i class="metismenu-icon"></i>
                                    ASIAROPE
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '11']) }}">
                                    <i class="metismenu-icon"></i>
                                    Cambridge
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '2']) }}">
                                    <i class="metismenu-icon"></i>
                                    GMEC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '4']) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA
                                </a>
                            </li>    <li>
                                <a href="{{ route('receipts', ['sfield' => '13']) }}">
                                    <i class="metismenu-icon"></i>
                                    ISOCSEA
                                </a>
                            </li>    <li>
                                <a href="{{ route('receipts', ['sfield' => '14']) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA+ISOCSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '5']) }}">
                                    <i class="metismenu-icon"></i>
                                    INFINITE Spelling Bee
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '6']) }}">
                                    <i class="metismenu-icon"></i>
                                    GELOSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '7']) }}">
                                    <i class="metismenu-icon"></i>
                                    LIMOC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '8']) }}">
                                    <i class="metismenu-icon"></i>
                                    STEM
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '15']) }}">
                                    <i class="metismenu-icon"></i>
                                    Huaxiacup
                                </a>
                            </li>
                            {{-- <li>
                                <a href="{{ route('receipts', ['sfield' => '16']) }}">
                                    <i class="metismenu-icon"></i>
                                    Mathleague
                                    <!-- ml -->
                                </a>
                            </li> --}}
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '0']) }}">
                                    <i class="metismenu-icon"></i>
                                    Img Reward
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => 'TT']) }}">
                                    <i class="metismenu-icon"></i>
                                    Test Field
                                </a>
                            </li>

                        </ul>
                    </li>

                    <li>
                        @php
                        {{
                            $ymonth =date("Y-m");
                            $pmd = date("Y");
                        }}
                        @endphp
                        <a href="#">
                            <i class="metismenu-icon pe-7s-graph2"></i>
                            Receipt Report
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '9','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร OLPCS รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    OLPCS
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '10','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร ASIAROPE รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    ASIAROPE
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '11','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร Cambridge รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    Cambridge
                                </a>
                            </li>
                            <li>


                                <a href="{{ route('receipts.print_month', ['pmsfield' => '2','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร GMEC รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    GMEC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '4','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร IMOCSEA รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '5','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร INFINITE Spelling Bee รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    INFINITE Spelling Bee
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '6','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร GELOSEA รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    GELOSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '7','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร LIMOC รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    LIMOC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts.print_month', ['pmsfield' => '8','type' => 'report_view','print_month' => $ymonth,'Pmdescription'=> 'ค่าสมัคร STEM รอบแรก '. $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    STEM
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif


                @if (Auth::user()->level == 1 || Auth::user()->level == 0)
                <li class="app-sidebar__heading">ผู้ดูแลระบบ</li>
                <li>
                    <a href="#">
                        <i class="metismenu-icon pe-7s-user"></i>
                        พนักงาน
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>

                    <ul>
                        <li>
                            <a href="{{ route('users') }}">
                                <i class="metismenu-icon"></i>
                                รายชื่อพนักงาน
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="metismenu-icon pe-7s-users"></i>
                        สรุปยอดนักเรียน
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>

                    <ul>
                        <li>
                            <a href="{{ route('report-student') }}">
                                <i class="metismenu-icon"></i>
                                รายงานยอดนักเรียน
                            </a>
                        </li>

                        {{-- แก้ เช็คชื่อ พี่หนึ่ง && me --}}
                        @if (Auth::user()->level == 0 && Auth::user()->id == 4 || Auth::user()->id == 16)
                        <li>
                            <a href="{{ route('reportMonthStd') }}">
                                <i class="metismenu-icon"></i>
                                รายงานประจำเดือน
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reportRecieptStd') }}">
                                <i class="metismenu-icon"></i>
                                Report Student Recieept
                            </a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif

                {{-- <li class="app-sidebar__heading">UI Components</li>
                <li>
                    <a href="#">
                        <i class="metismenu-icon pe-7s-diamond"></i>
                        Elements
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="elements-buttons-standard.html">
                                <i class="metismenu-icon"></i>
                                Buttons
                            </a>
                        </li>
                        <li>
                            <a href="elements-dropdowns.html">
                                <i class="metismenu-icon">
                                </i>Dropdowns
                            </a>
                        </li>
                        <li>
                            <a href="elements-icons.html">
                                <i class="metismenu-icon">
                                </i>Icons
                            </a>
                        </li>
                        <li>
                            <a href="elements-badges-labels.html">
                                <i class="metismenu-icon">
                                </i>Badges
                            </a>
                        </li>
                        <li>
                            <a href="elements-cards.html">
                                <i class="metismenu-icon">
                                </i>Cards
                            </a>
                        </li>
                        <li>
                            <a href="elements-list-group.html">
                                <i class="metismenu-icon">
                                </i>List Groups
                            </a>
                        </li>
                        <li>
                            <a href="elements-navigation.html">
                                <i class="metismenu-icon">
                                </i>Navigation Menus
                            </a>
                        </li>
                        <li>
                            <a href="elements-utilities.html">
                                <i class="metismenu-icon">
                                </i>Utilities
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="metismenu-icon pe-7s-car"></i>
                        Components
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="components-tabs.html">
                                <i class="metismenu-icon">
                                </i>Tabs
                            </a>
                        </li>
                        <li>
                            <a href="components-accordions.html">
                                <i class="metismenu-icon">
                                </i>Accordions
                            </a>
                        </li>
                        <li>
                            <a href="components-notifications.html">
                                <i class="metismenu-icon">
                                </i>Notifications
                            </a>
                        </li>
                        <li>
                            <a href="components-modals.html">
                                <i class="metismenu-icon">
                                </i>Modals
                            </a>
                        </li>
                        <li>
                            <a href="components-progress-bar.html">
                                <i class="metismenu-icon">
                                </i>Progress Bar
                            </a>
                        </li>
                        <li>
                            <a href="components-tooltips-popovers.html">
                                <i class="metismenu-icon">
                                </i>Tooltips &amp; Popovers
                            </a>
                        </li>
                        <li>
                            <a href="components-carousel.html">
                                <i class="metismenu-icon">
                                </i>Carousel
                            </a>
                        </li>
                        <li>
                            <a href="components-calendar.html">
                                <i class="metismenu-icon">
                                </i>Calendar
                            </a>
                        </li>
                        <li>
                            <a href="components-pagination.html">
                                <i class="metismenu-icon">
                                </i>Pagination
                            </a>
                        </li>
                        <li>
                            <a href="components-scrollable-elements.html">
                                <i class="metismenu-icon">
                                </i>Scrollable
                            </a>
                        </li>
                        <li>
                            <a href="components-maps.html">
                                <i class="metismenu-icon">
                                </i>Maps
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="tables-regular.html">
                        <i class="metismenu-icon pe-7s-display2"></i>
                        Tables
                    </a>
                </li>
                <li class="app-sidebar__heading">Widgets</li>
                <li>
                    <a href="dashboard-boxes.html">
                        <i class="metismenu-icon pe-7s-display2"></i>
                        Dashboard Boxes
                    </a>
                </li>
                <li class="app-sidebar__heading">Forms</li>
                <li>
                    <a href="forms-controls.html">
                        <i class="metismenu-icon pe-7s-mouse">
                        </i>Forms Controls
                    </a>
                </li>
                <li>
                    <a href="forms-layouts.html">
                        <i class="metismenu-icon pe-7s-eyedropper">
                        </i>Forms Layouts
                    </a>
                </li>
                <li>
                    <a href="forms-validation.html">
                        <i class="metismenu-icon pe-7s-pendrive">
                        </i>Forms Validation
                    </a>
                </li>
                <li class="app-sidebar__heading">Charts</li>
                <li>
                    <a href="charts-chartjs.html">
                        <i class="metismenu-icon pe-7s-graph2">
                        </i>ChartJS
                    </a>
                </li>
                <li class="app-sidebar__heading">PRO Version</li>
                <li>
                    <a href="https://dashboardpack.com/theme-details/architectui-dashboard-html-pro/" target="_blank">
                        <i class="metismenu-icon pe-7s-graph2">
                        </i>
                        Upgrade to PRO
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</div>
