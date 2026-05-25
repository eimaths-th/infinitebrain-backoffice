@php
    use App\Models\Subject;
    use App\Models\SubjectPrice;
    use App\Models\Godeye;
    use App\Models\FormManage;
    use App\Models\PaymentVoucher;
    use App\Models\ReceiptVoucher;
    use App\Models\LeaveRequest;

    $formManages = FormManage::all();

    // คำนวณจำนวนแจ้งเตือนสำหรับใบสำคัญจ่าย (Payment Voucher)
    $pending_pv_count = 0;
    if (Auth::user()->can('payment-voucher.view')) {
        // รอตรวจสอบ
        if (Auth::user()->can('payment-voucher.review')) {
            $pending_pv_count += PaymentVoucher::where('status', PaymentVoucher::STATUS_PENDING)
                ->where('voucher_no', 'not like', 'MT%')
                ->count();
        }
        // รออนุมัติ
        if (Auth::user()->can('payment-voucher.approve')) {
            $pending_pv_count += PaymentVoucher::where('status', PaymentVoucher::STATUS_REVIEWED)->count();
        }
        // รอจ่ายเงิน (เพิ่มตามความต้องการผู้ใช้)
        $pending_pv_count += PaymentVoucher::where('status', PaymentVoucher::STATUS_APPROVED)->count();
    }

    // คำนวณจำนวนแจ้งเตือนสำหรับใบสำคัญรับ (Receipt Voucher)
    $pending_rv_count = 0;
    if (Auth::user()->can('receipt-voucher.view')) {
        if (Auth::user()->can('receipt-voucher.verify')) {
            $pending_rv_count += ReceiptVoucher::where('status', ReceiptVoucher::STATUS_PENDING)->count();
        }
        if (Auth::user()->can('receipt-voucher.complete')) {
            $pending_rv_count += ReceiptVoucher::where('status', ReceiptVoucher::STATUS_VERIFIED)->count();
        }
    }

    // คำนวณจำนวนแจ้งเตือนสำหรับใบลา (Leave Request)
    $pending_leave_count = 0;
    if (Auth::user()->can('system.view') || Auth::user()->hasRole(['super-admin', 'admin'])) {
        $pending_leave_count = LeaveRequest::where('status', 'pending')->count();
    }
    // ยอดรวม Finance
    $finance_total_count = $pending_pv_count + $pending_rv_count;
@endphp

<style>
    .premium-badge {
        background: linear-gradient(135deg, #ff0844 0%, #ff0844 100%);
        color: white !important;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        margin-left: auto;
        margin-right: 8px;
        display: inline-block;
        line-height: 1.4;
        min-width: 18px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .mm-active > a > .premium-badge {
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        transform: scale(1.05);
    }
</style>
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
                        <a href="{{ route('receipt_school.sourceReport') }}">
                            <i class="metismenu-icon pe-7s-graph2"></i>
                            รายงานแหล่งที่มา (Source)
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
                            <li>
                                <a href="{{ route('promotions') }}">
                                    <i class="metismenu-icon"></i>
                                    Promotion
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
                            @if(Auth::user()->can('leave-requests.view') || !Auth::user()->hasRole(['super-admin', 'admin']))
                                @php 
                                    $user_pending_leave = \App\Models\LeaveRequest::where('user_id', Auth::id())->where('status', 'pending')->count();
                                @endphp
                                @if($user_pending_leave > 0)
                                    <span class="premium-badge">{{ $user_pending_leave }}</span>
                                @endif
                                @if($pending_leave_count > 0)
                                    <span class="premium-badge">{{ $pending_leave_count }}</span>
                                @endif
                            @endif
                        </a>
                    </li>
                @endcan

                @can('payment-voucher.view')

                    <li>
                        <a href="#">
                            <i class="metismenu-icon  pe-7s-wallet"></i>
                            Finance
                            @if($finance_total_count > 0)
                                <span class="premium-badge">{{ $finance_total_count }}</span>
                            @endif
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
                                    @if($pending_pv_count > 0)
                                        <span class="premium-badge">{{ $pending_pv_count }}</span>
                                    @endif
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
                                    @can('payment-voucher-category.view')
                                    <li>
                                        <a href="{{ route('payment-voucher-category.index') }}">
                                            <i class="metismenu-icon"></i>
                                            จัดการหมวดหมู่ใบสำคัญจ่าย
                                        </a>
                                    </li>
                                    @endcan

                                    <li class="mm-divider" style="height: 1px; background: rgba(0,0,0,0.05); margin: 5px 20px;"></li>

                                    @can('payment-voucher.review')
                                        <li>
                                            <a href="{{ route('payment-voucher.approved') }}">
                                                <i class="metismenu-icon"></i>
                                                รอตรวจสอบ
                                                @php $c = PaymentVoucher::where('status', PaymentVoucher::STATUS_PENDING)->where('voucher_no', 'not like', 'MT%')->count(); @endphp
                                                @if($c > 0) <span class="premium-badge">{{ $c }}</span> @endif
                                            </a>
                                        </li>
                                    @endcan

                                    @can('payment-voucher.approve')
                                        <li>
                                            <a href="{{ route('payment-voucher.pending') }}">
                                                <i class="metismenu-icon"></i>
                                                รออนุมัติ
                                                @php $c = PaymentVoucher::where('status', PaymentVoucher::STATUS_REVIEWED)->count(); @endphp
                                                @if($c > 0) <span class="premium-badge" style="background: #f7b924; color: #000 !important; font-size: 9px;">{{ $c }}</span> @endif
                                            </a>
                                        </li>
                                    @endcan

                                    <li>
                                        <a href="{{ route('payment-voucher.index', ['status' => 'approved']) }}">
                                            <i class="metismenu-icon"></i>
                                            รอจ่ายเงิน
                                            @php $c = PaymentVoucher::where('status', PaymentVoucher::STATUS_APPROVED)->count(); @endphp
                                            @if($c > 0) <span class="premium-badge">{{ $c }}</span> @endif
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @can('receipt-voucher.view')
                                <li>
                                    <a href="#">
                                        <i class="metismenu-icon pe-7s-note2"></i>
                                        ใบสำคัญรับ
                                        @if($pending_rv_count > 0)
                                            <span class="premium-badge">{{ $pending_rv_count }}</span>
                                        @endif
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
                @can(['teacher-revenue.view'])
                    <li class="{{ request()->routeIs('teacher-revenue.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('teacher-revenue.index') }}" class="{{ request()->routeIs('teacher-revenue.*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-note2"></i>
                            รายได้ครู
                        </a>
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
                                    <a href="{{ route('admin.branch-groups.index') }}">
                                        <i class="metismenu-icon"></i>
                                        จัดการกลุ่มสาขา
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.branches.index') }}">
                                        <i class="metismenu-icon"></i>
                                        จัดการสาขา
                                    </a>
                                </li>
                            </ul>
                        @endcan

                        @can('system.view')
                            <ul>
                                <li>
                                    <a href="{{ route('leave-requests.index') }}">
                                        <i class="metismenu-icon"></i>
                                        ตรวจสอบใบลา
                                        @if($pending_leave_count > 0)
                                            <span class="premium-badge">{{ $pending_leave_count }}</span>
                                        @endif
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var currentUrl = window.location.href.split('?')[0].split('#')[0];
        
        // Find all links in the sidebar
        var links = document.querySelectorAll('.vertical-nav-menu a');
        var matchedLink = null;
        var longestMatch = 0;

        links.forEach(function(link) {
            var rawHref = link.getAttribute('href');
            // Ignore empty links, hash links, or javascript
            if (!rawHref || rawHref === '#' || rawHref.startsWith('javascript')) return;

            var href = link.href.split('?')[0].split('#')[0];

            // Check if current URL starts with the link's href
            if (currentUrl.startsWith(href)) {
                // Find the most specific match (longest href)
                if (href.length > longestMatch) {
                    matchedLink = link;
                    longestMatch = href.length;
                }
            }
        });

        if (matchedLink) {
            // Add active class to the matched link
            matchedLink.classList.add('mm-active');
            
            // Setup parent elements
            var currentElement = matchedLink;
            while (currentElement) {
                if (currentElement.tagName === 'LI') {
                    currentElement.classList.add('mm-active');
                } else if (currentElement.tagName === 'UL' && currentElement.classList.contains('vertical-nav-menu') === false) {
                    // It's a submenu (dropdown)
                    currentElement.classList.add('mm-show');
                    
                    // Also need to set the parent a tag to active
                    var parentLi = currentElement.parentElement;
                    if (parentLi && parentLi.tagName === 'LI') {
                        parentLi.classList.add('mm-active');
                        var parentA = parentLi.querySelector('a:first-child');
                        if (parentA) {
                            parentA.classList.add('mm-active');
                        }
                    }
                }
                
                currentElement = currentElement.parentElement;
                
                // Stop when we reach the top-level menu container
                if (currentElement && currentElement.classList.contains('vertical-nav-menu')) {
                    break;
                }
            }
        }
    });
</script>
