@php
    use App\Models\Subject;
    use App\Models\SubjectPrice;
    use App\Models\Godeye;
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
                @if (Auth::user()->level == 2 || Auth::user()->level == 1 || Auth::user()->level == 0)
                    <li class="app-sidebar__heading"> </li>
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
                                $data = Subject::orderBy('id', 'asc')->get();
                                $dataGroup = SubjectPrice::orderBy('id', 'asc')
                                    ->get()
                                    ->groupBy('subject_id');
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
                            <li>
                                <a href="{{ route('receipts_form', ['sfield' => '16']) }}">
                                    <i class="metismenu-icon"></i>
                                    Mathleague
                                    <!-- ml -->
                                </a>
                            </li>

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
                            </li>
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '13']) }}">
                                    <i class="metismenu-icon"></i>
                                    ISOCSEA
                                </a>
                            </li>
                            <li>
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
                            <li>
                                <a href="{{ route('receipts', ['sfield' => '16']) }}">
                                    <i class="metismenu-icon"></i>
                                    Mathleague
                                    <!-- ml -->
                                </a>
                            </li>
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

                            $ymonth = date('Y-m');
                            $pmd = date('Y');

                        @endphp
                        <a href="#">
                            <i class="metismenu-icon pe-7s-graph2"></i>
                            Receipt Report
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '9', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร OLPCS รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    OLPCS
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '10', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร ASIAROPE รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    ASIAROPE
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '11', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร Cambridge รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    Cambridge
                                </a>
                            </li>
                            <li>


                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '2', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร GMEC รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    GMEC
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '4', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร IMOCSEA รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '5', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร INFINITE Spelling Bee รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    INFINITE Spelling Bee
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '6', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร GELOSEA รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    GELOSEA
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '7', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร LIMOC รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    LIMOC
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('receipts.print_month', ['pmsfield' => '8', 'type' => 'report_view', 'print_month' => $ymonth, 'Pmdescription' => 'ค่าสมัคร STEM รอบแรก ' . $pmd]) }}">
                                    <i class="metismenu-icon"></i>
                                    STEM
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif
                @if (Auth::user()->level != 3)
                    <li class="app-sidebar__heading"> ผนักงาน </li>

                    <li>
                        <a href="{{ route('home2') }}">
                            <i class="metismenu-icon pe-7s-home"></i>
                            Home
                        </a>
                    </li>
                @endif

                @if (Auth::user()->level == 1 || Auth::user()->level == 0 || Auth::user()->level == 4)
                    <li class="app-sidebar__heading">ผู้ดูแลระบบ</li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-user"></i>
                            พนักงาน
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        @if (Auth::user()->level == 1 || Auth::user()->level == 0)
                            <ul>
                                <li>
                                    <a href="{{ route('users') }}">
                                        <i class="metismenu-icon"></i>
                                        รายชื่อพนักงาน
                                    </a>
                                </li>
                            </ul>
                        @endif
                        <ul>
                            <li>
                                <a href="{{ route('recordtime.index') }}">
                                    <i class="metismenu-icon"></i>
                                    บันทึกเข้างาน
                                </a>
                            </li>
                        </ul>
                        @if (Auth::user()->level == 0 && Auth::user()->id == 4)
                            <ul>
                                <li>
                                    <a href="{{ route('Godeye.index') }}">
                                        <i class="metismenu-icon"></i>
                                        God Eye
                                    </a>
                                </li>
                            </ul>
                        @endif
                    </li>
                    @if (Auth::user()->level == 1 || Auth::user()->level == 0)
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

                                <!-- แก้ เช็คชื่อ พี่หนึ่ง && me -->
                                @php
                                    $user_id = Auth::user()->id;

                                    $godEye = Godeye::where('user_id', $user_id)->get();
                                @endphp
                                @if ((Auth::user()->level == 0 && Auth::user()->id == 4) || $godEye->count() > 0)
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
                                    <li>
                                        <a href="{{ route('reportTime') }}">
                                            <i class="metismenu-icon"></i>
                                            รายงานการมาทำงาน
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>
                    @endif
                @endif

            </ul>
        </div>
    </div>
</div>
