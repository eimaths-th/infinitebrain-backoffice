@php
    use App\Models\Subject;
    use App\Models\SubjectPrice;
    use App\Models\Godeye;
    use App\Models\FormManage;
    $formManages = FormManage::all();
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
                @can(['general.admin.view'])
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
                                $dataGroup = SubjectPrice::orderBy('id', 'asc')->get()->groupBy('subject_id');
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
                @endcan
                {{-- ส่วนพนักงาน - แสดงทุก role ยกเว้น parent --}}

                <li class="app-sidebar__heading"> ทั่วไป </li>
                @can(['general.view'])
                    <li>
                        <a href="{{ route('home2') }}">
                            <i class="metismenu-icon pe-7s-home"></i>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('leave-requests.index') }}">
                            <i class="metismenu-icon pe-7s-note2"></i>
                            เขียนใบลา
                        </a>
                    </li>
                @endcan

                @can('payment-voucher.view')

                    <li>
                        <a href="#">
                            <i class="metismenu-icon  pe-7s-wallet"></i>
                            Finance
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                            @can('payment-voucher.dashboard')
                                <li>
                                    <a href="{{ route('finance-dashboard.index') }}">
                                        <i class="metismenu-icon pe-7s-graph1"></i>
                                        Finance Dashboard
                                    </a>
                                </li>
                            @endcan
                            @can('performance.view')
                                <li>
                                    <a href="{{ route('performance.index') }}">
                                        <i class="metismenu-icon pe-7s-graph1"></i>
                                        ผลประกอบการ
                                    </a>
                                </li>
                            @endcan
                            {{-- <li>
                                    <a href="{{ route('payment-voucher.index') }}">
                                        <i class="metismenu-icon"></i>
                                        ใบสำคัญจ่าย
                                    </a>
                                </li> --}}
                            <li>
                                <a href="#">
                                    <i class="metismenu-icon pe-7s-wallet"></i>
                                    ใบสำคัญจ่าย
                                    <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                </a>
                                <ul>
                                    <li>
                                        <a href="{{ route('payment-voucher.index') }}">
                                            <i class="metismenu-icon"></i>
                                            รายการทั้งหมด
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('payment-voucher.create') }}">
                                            <i class="metismenu-icon"></i>
                                            สร้างใบสำคัญจ่าย
                                        </a>
                                    </li>
                                    @can('payment-voucher.approve')
                                        <li>
                                            <a href="{{ route('payment-voucher.pending') }}">
                                                <i class="metismenu-icon"></i>
                                                รออนุมัติ
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                            @can('receipt-voucher.view')
                            <li>
                                <a href="#">
                                    <i class="metismenu-icon pe-7s-note2"></i>
                                    ใบสำคัญรับ
                                    <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                </a>
                                <ul>
                                    <li>
                                        <a href="{{ route('receipt-voucher.index') }}">
                                            <i class="metismenu-icon"></i>
                                            รายการทั้งหมด
                                        </a>
                                    </li>
                                    @can('receipt-voucher.create')
                                    <li>
                                        <a href="{{ route('receipt-voucher.create') }}">
                                            <i class="metismenu-icon"></i>
                                            สร้างใบสำคัญรับ
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan
                        </ul>
                        @can('payroll.view')
                            <ul>
                                {{-- <li>
                                    <a href="{{ route('payment-voucher.index') }}">
                                        <i class="metismenu-icon"></i>
                                        ใบสำคัญจ่าย
                                    </a>
                                </li> --}}

                                <li>

                                    <a href="#">
                                        <i class="metismenu-icon pe-7s-wallet"></i>
                                        เงินเดือนพนักงาน
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>

                                        <li>
                                            <a href="{{ route('payroll.index') }}">
                                                <i class="metismenu-icon"></i>
                                                รายการทั้งหมด
                                            </a>
                                        </li>

                                    </ul>

                                </li>

                            </ul>
                        @endcan


                        @can('bank-statement.view')
                            <ul>
                                {{-- <li>
                                    <a href="{{ route('payment-voucher.index') }}">
                                        <i class="metismenu-icon"></i>
                                        ใบสำคัญจ่าย
                                    </a>
                                </li> --}}

                                <li>

                                    <a href="#">
                                        <i class="metismenu-icon pe-7s-wallet"></i>
                                        Bank Statement
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>

                                        <li>
                                            <a href="{{ route('bank-statement.index') }}">
                                                <i class="metismenu-icon"></i>
                                                รายการทั้งหมด
                                            </a>
                                        </li>

                                    </ul>

                                </li>

                            </ul>
                        @endcan
                    </li>
                @endcan

                @can('recruitment.view')
                    <li class="app-sidebar__heading">HR / รับสมัครงาน</li>
                    <li>
                        <a href="{{ route('recruitment.index') }}">
                            <i class="metismenu-icon pe-7s-add-user"></i>
                            รายชื่อผู้สมัคร
                        </a>
                    </li>
                @endcan

                {{-- ส่วนผู้ดูแลระบบ --}}

                @can(['general.admin.view'])
                    <li class="app-sidebar__heading">ผู้ดูแลระบบ</li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-user"></i>
                            พนักงาน
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        @hasanyrole(['super-admin', 'admin'])
                            <ul>
                                <li>
                                    <a href="{{ route('users') }}">
                                        <i class="metismenu-icon"></i>
                                        รายชื่อพนักงาน
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user-tags.index') }}">
                                        <i class="metismenu-icon"></i>
                                        จัดการ Tags
                                    </a>
                                </li>
                            </ul>
                            <ul>
                                <li>
                                    <a href="{{ route('company.holidays.index') }}">
                                        <i class="metismenu-icon"></i>
                                        วันหยุดบริษัท
                                    </a>
                                </li>
                            </ul>
                            <ul>
                                <li>
                                    <a href="{{ route('app-down-load.index') }}">
                                        <i class="metismenu-icon"></i>
                                        App Download
                                    </a>
                                </li>
                            </ul>
                            <ul>
                                <li>
                                    <a href="{{ route('recordtime.index') }}">
                                        <i class="metismenu-icon"></i>
                                        บันทึกเข้างาน
                                    </a>
                                </li>

                            </ul>
                            <ul>
                                <li>
                                    <a href="{{ route('admin.user-roles.index') }}">
                                        <i class="metismenu-icon"></i>
                                        กำหนด Role ให้ผู้ใช้
                                    </a>
                                </li>
                            </ul>
                        @endhasanyrole

                        @can('system.view')
                            <ul>
                                <li>
                                    <a href="{{ route('leave-requests.index') }}">
                                        <i class="metismenu-icon"></i>
                                        ตรวจสอบใบลา
                                    </a>
                                </li>
                            </ul>
                        @endcan



                        {{-- Role & Permission Management (Super Admin Only) --}}
                        @can('system.view')
                        <li class="app-sidebar__heading">จัดการสิทธิ์</li>
                        <li>
                            <a href="#">
                                <i class="metismenu-icon pe-7s-shield"></i>
                                Role & Permission
                                <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('admin.roles.index') }}">
                                        <i class="metismenu-icon"></i>
                                        จัดการ Roles
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.permissions.index') }}">
                                        <i class="metismenu-icon"></i>
                                        จัดการ Permissions
                                    </a>
                                </li>

                            </ul>
                        @endcan

                    </li>

                    {{-- <ul>

                    </ul> --}}
                    @if (Auth::user()->hasRole('super-admin') && Auth::user()->id == 4)
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
                    @can(['system.view'])
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

                            </ul>
                        </li>
                    @endcan
                    @php
                        $user_id = Auth::user()->id;

                        $godEye = Godeye::where('user_id', $user_id)->get();
                    @endphp
                    @if (Auth::user()->hasRole('super-admin') || $godEye->count() > 0)
                        <li>
                            <a href="#">
                                <i class="metismenu-icon pe-7s-users"></i>
                                Manager
                                <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                            </a>

                            <ul>

                                <!-- แก้ เช็คชื่อ พี่หนึ่ง && me -->
                                @php
                                    $user_id = Auth::user()->id;

                                    $godEye = Godeye::where('user_id', $user_id)->get();
                                @endphp
                                <li>
                                    <a href="{{ route('students.expire') }}">
                                        <i class="metismenu-icon"></i>
                                        Students-Expire
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('reportMonthStd') }}">
                                        <i class="metismenu-icon"></i>
                                        ค่าสมัครเรียนประจำเดือน
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('reportYearStd') }}">
                                        <i class="metismenu-icon"></i>
                                        ค่าสมัครเรียนประจำปี
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('reportTime') }}">
                                        <i class="metismenu-icon"></i>
                                        รายงานการมาทำงาน
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('timelogs') }}">
                                        <i class="metismenu-icon"></i>
                                        ประวัติการแก้ไขเวลาเข้างาน
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('studentBonus.index') }}">
                                        <i class="metismenu-icon"></i>
                                        Bonus Student
                                    </a>
                                </li>
                                {{-- @if (Auth::user()->id == 16) --}}
                                <li>
                                    <a href="{{ route('reportRecieptStd') }}">
                                        <i class="metismenu-icon"></i>
                                        Report Student Recieept
                                    </a>
                                </li>
                                {{-- @endif --}}
                            </ul>
                        </li>
                    @endif
                @endcan

            </ul>
        </div>
    </div>
</div>
