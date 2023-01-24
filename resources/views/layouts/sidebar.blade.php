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
                {{--<li class="app-sidebar__heading">Dashboards</li>
                <li>
                    <a href="{{ route('users') }}" class="mm-active">
                        <i class="metismenu-icon pe-7s-rocket"></i>
                        Dashboard
                    </a>
                </li>--}}
                @if (Auth::user()->level == 1)
                    <li class="app-sidebar__heading">Admin</li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Employees
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                            <li>
                                <a href="{{ route('users') }}">
                                    <i class="metismenu-icon"></i>
                                    Users
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (Auth::user()->level == 2 || Auth::user()->level == 1)
                    <li class="app-sidebar__heading">School</li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-network"></i>
                            Level
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                           {{-- @foreach ($data as $key)
                                <li>
                                    <a href="{{ route('levels', ['id' => $key->id]) }}">
                                        <i class="metismenu-icon"></i>
                                        {{ $key->title }}
                                    </a>
                                </li>
                            @endforeach --}}
                            <li>
                                <a href="{{ route('levels', ['id' => '1']) }}">
                                    <i class="metismenu-icon"></i>
                                    Math
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('levels' , ['id' => '2']) }}">
                                    <i class="metismenu-icon"></i>
                                    Play Math
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('levels' , ['id' => '3']) }}">
                                    <i class="metismenu-icon"></i>
                                    Private Math
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('levels' , ['id' => '4']) }}">
                                    <i class="metismenu-icon"></i>
                                    English A-I
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('levels' , ['id' => '5']) }}">
                                    <i class="metismenu-icon"></i>
                                    English 5-8
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('levels' , ['id' => '6']) }}">
                                    <i class="metismenu-icon"></i>
                                    English Spark
                                </a>
                            </li>
                        </ul>
                        
                    </li>

                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-study"></i>
                            Student
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>

                        <ul>
                            <li>
                                <a href="{{ route('students') }}">
                                    <i class="metismenu-icon"></i>
                                    Students
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('course') }}">
                                    <i class="metismenu-icon"></i>
                                    Study Course
                                </a>
                            </li>
                        </ul>
                        
                    </li>
                @endif

                @if (Auth::user()->level == 3 || Auth::user()->level == 1)
                    <li class="app-sidebar__heading">Account</li>
                    <li>
                        <a href="#">
                            <i class="metismenu-icon pe-7s-note2"></i>
                            Receipt
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
                                <a href="{{ route('receipts', ['sfield' => '2']) }}">
                                    <i class="metismenu-icon"></i>
                                    GMEC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts' , ['sfield' => '4']) }}">
                                    <i class="metismenu-icon"></i>
                                    IMOCSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts' , ['sfield' => '5']) }}">
                                    <i class="metismenu-icon"></i>
                                    INFINITE Spelling Bee
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts' , ['sfield' => '6']) }}">
                                    <i class="metismenu-icon"></i>
                                    GELOSEA
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts' , ['sfield' => '7']) }}">
                                    <i class="metismenu-icon"></i>
                                    LIMOC
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receipts' , ['sfield' => '8']) }}">
                                    <i class="metismenu-icon"></i>
                                    STEM
                                </a>
                            </li>
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
