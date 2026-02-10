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

    <!-- Flatpickr Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


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

    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/infinite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-mobile-fix.css') }}">

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

    {{-- <link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="../assets/plugins/select2/dist/js/select2.min.js"></script> --}}
</head>

<body>
    <div class="app-container app-theme-white body-tabs-shadow fixed-header">

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

                    <!-- Edit User Modal -->
                    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog"
                        aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditUserModal">
                        <div class="modal-dialog modal-lg" role="document">
                            <form class="form" id="EditUserForm" action="" method="POST">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">
                                            <i class="fa fa-user-circle me-2"></i>แก้ไขข้อมูลส่วนตัว
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close">
                                        </button>
                                    </div>
                                    <!-- Modal body -->
                                    <div class="modal-body">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                            style="display: none;">
                                            <button type="button" class="btn-close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                                            style="display: none;">
                                            <button type="button" class="btn-close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true"></span>
                                            </button>
                                        </div>

                                        <!-- Tabs -->
                                        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="account-tab" data-bs-toggle="tab"
                                                    data-bs-target="#account-pane" type="button" role="tab"
                                                    aria-controls="account-pane" aria-selected="true">
                                                    <i class="fa fa-key me-1"></i>ข้อมูลบัญชี
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="personal-tab" data-bs-toggle="tab"
                                                    data-bs-target="#personal-pane" type="button" role="tab"
                                                    aria-controls="personal-pane" aria-selected="false">
                                                    <i class="fa fa-id-card me-1"></i>ข้อมูลส่วนตัว
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="bank-tab" data-bs-toggle="tab"
                                                    data-bs-target="#bank-pane" type="button" role="tab"
                                                    aria-controls="bank-pane" aria-selected="false">
                                                    <i class="fa fa-university me-1"></i>ข้อมูลธนาคาร
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content pt-3" id="profileTabsContent">
                                            <!-- Account Tab -->
                                            <div class="tab-pane fade show active" id="account-pane" role="tabpanel"
                                                aria-labelledby="account-tab">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="EdituName" class="form-label">ชื่อ-นามสกุล <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="name"
                                                            id="EdituName" value="{{ Auth::user()->name }}" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="EdituEmail" class="form-label">Email <span
                                                                class="text-danger">*</span></label>
                                                        <input type="email" class="form-control" name="email"
                                                            id="EdituEmail" value="{{ Auth::user()->email }}"
                                                            required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="EdituPassword" class="form-label">รหัสผ่านใหม่
                                                            <small
                                                                class="text-muted">(เว้นว่างหากไม่เปลี่ยน)</small></label>
                                                        <input type="password" class="form-control" name="password"
                                                            id="EdituPassword" autocomplete="new-password">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="EdituPasswordC"
                                                            class="form-label">ยืนยันรหัสผ่าน</label>
                                                        <input type="password" class="form-control"
                                                            name="password_confirmation" id="EdituPasswordC"
                                                            autocomplete="new-password">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Personal Tab -->
                                            <div class="tab-pane fade" id="personal-pane" role="tabpanel"
                                                aria-labelledby="personal-tab">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="EdituTitlePrefix"
                                                            class="form-label">คำนำหน้า</label>
                                                        <select class="form-control" name="title_prefix"
                                                            id="EdituTitlePrefix">
                                                            <option value="">-- เลือก --</option>
                                                            @foreach (\App\Models\User::TITLE_PREFIXES as $key => $label)
                                                                <option value="{{ $key }}">
                                                                    {{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="EdituNickname" class="form-label">ชื่อเล่น</label>
                                                        <input type="text" class="form-control" name="nickname"
                                                            id="EdituNickname">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="EdituGender" class="form-label">เพศ</label>
                                                        <select class="form-control" name="gender" id="EdituGender">
                                                            <option value="">-- เลือก --</option>
                                                            <option value="male">ชาย</option>
                                                            <option value="female">หญิง</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="EdituBirthDate" class="form-label">วันเกิด</label>
                                                        <input type="text" class="form-control datepicker"
                                                            name="birth_date" id="EdituBirthDate">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="EdituNationality"
                                                            class="form-label">สัญชาติ</label>
                                                        <input type="text" class="form-control" name="nationality"
                                                            id="EdituNationality" value="ไทย">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="EdituPhone"
                                                            class="form-label">เบอร์โทรศัพท์</label>
                                                        <input type="text" class="form-control" name="phone"
                                                            id="EdituPhone">
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label for="EdituAddress" class="form-label">ที่อยู่</label>
                                                        <textarea class="form-control" name="address" id="EdituAddress" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bank Tab -->
                                            <div class="tab-pane fade" id="bank-pane" role="tabpanel"
                                                aria-labelledby="bank-tab">
                                                <div class="row">
                                                    <div class="col-12 mb-3">
                                                        <div class="alert alert-info">
                                                            <i class="fa fa-info-circle me-2"></i>
                                                            ข้อมูลธนาคารจะใช้สำหรับการโอนเงินเดือน กรุณากรอกให้ถูกต้อง
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="EdituBankName" class="form-label">ธนาคาร</label>
                                                        <input type="text" class="form-control" name="bank_name"
                                                            id="EdituBankName"
                                                            placeholder="เช่น กสิกรไทย, กรุงเทพ, ไทยพาณิชย์">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="EdituBankAccount"
                                                            class="form-label">เลขที่บัญชี</label>
                                                        <input type="text" class="form-control"
                                                            name="bank_account" id="EdituBankAccount"
                                                            placeholder="เลขที่บัญชีธนาคาร">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- Modal footer -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-success" id="SubmitUserEditForm"><i
                                                class="fa fa-save me-1"></i>บันทึก</button>
                                        <button type="button" class="btn btn-secondary modelClose"
                                            data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>ปิด</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @include('layouts.modal-form')

                </div>
                <!-- Footer -->
                @include('layouts.footer')
            </div>

        </div>
    </div>


    @yield('script')
    <script>
        //ตัวนี้จะเอาไว้ set ค่าต่างๆ toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>

    <script>
        $(document).ready(function() {
            // Load user data when modal opens
            $('#EditUserModal').on('show.bs.modal', function() {
                $.ajax({
                    url: "{{ route('profile.data') }}",
                    method: 'GET',
                    success: function(result) {
                        if (result.success) {
                            var user = result.user;
                            $('#EdituName').val(user.name);
                            $('#EdituEmail').val(user.email);
                            $('#EdituNickname').val(user.nickname);
                            $('#EdituTitlePrefix').val(user.title_prefix);
                            $('#EdituGender').val(user.gender);
                            $('#EdituBirthDate').val(user.birth_date);
                            $('#EdituNationality').val(user.nationality || 'ไทย');
                            $('#EdituPhone').val(user.phone);
                            $('#EdituAddress').val(user.address);
                            $('#EdituBankName').val(user.bank_name);
                            $('#EdituBankAccount').val(user.bank_account);
                            // Clear password fields
                            $('#EdituPassword').val('');
                            $('#EdituPasswordC').val('');
                        }
                    },
                    error: function() {
                        toastr.error('ไม่สามารถโหลดข้อมูลได้');
                    }
                });
            });

            // Reset modal on close
            $('#EditUserModal').on('hidden.bs.modal', function() {
                $('#EditUserModal .alert-danger').html('').hide();
                $('#EditUserModal .alert-success').html('').hide();
                // Reset to first tab
                $('#account-tab').tab('show');
            });

            // Submit profile update
            $('#SubmitUserEditForm').click(function(e) {
                e.preventDefault();

                if (!confirm("ยืนยันการบันทึกข้อมูล?")) return;

                var $modal = $('#EditUserModal');
                $modal.find('.alert-danger').html('').hide();
                $modal.find('.alert-success').html('').hide();

                var formData = {
                    name: $('#EdituName').val(),
                    email: $('#EdituEmail').val(),
                    password: $('#EdituPassword').val(),
                    password_confirmation: $('#EdituPasswordC').val(),
                    nickname: $('#EdituNickname').val(),
                    title_prefix: $('#EdituTitlePrefix').val(),
                    gender: $('#EdituGender').val(),
                    birth_date: $('#EdituBirthDate').val(),
                    nationality: $('#EdituNationality').val(),
                    phone: $('#EdituPhone').val(),
                    address: $('#EdituAddress').val(),
                    bank_name: $('#EdituBankName').val(),
                    bank_account: $('#EdituBankAccount').val(),
                };

                $.ajax({
                    url: "{{ route('profile.update') }}",
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: function(result) {
                        if (result.success) {
                            $modal.find('.alert-success').html('<strong>' + result.message +
                                '</strong>').show();
                            toastr.success(result.message);
                            setTimeout(function() {
                                $modal.modal('hide');
                                // Reload page to reflect changes in header
                                location.reload();
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorHtml = '';
                            $.each(errors, function(key, value) {
                                errorHtml += '<li>' + value[0] + '</li>';
                            });
                            $modal.find('.alert-danger').html('<ul>' + errorHtml + '</ul>')
                                .show();
                        } else {
                            toastr.error('เกิดข้อผิดพลาด กรุณาลองใหม่');
                        }
                    }
                });
            });
        });
    </script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                locale: "th",
                allowInput: true
            });
        });
    </script>
</body>

</html>
