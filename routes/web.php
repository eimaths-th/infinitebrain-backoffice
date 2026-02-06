<?php

// namespace App\Http\Controllers;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AppDownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\EmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReceiptFormController;
use App\Http\Controllers\ParentRegisterController;
use App\Http\Controllers\SubjectPriceController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\GodeyeContreoller;
use App\Http\Controllers\ReceiptSchoolController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecordtimeController;
use App\Http\Controllers\RetrospectiveCheckController;
use App\Http\Controllers\RouteToggleController;
use App\Http\Controllers\StdlogController;
use App\Http\Controllers\SuperMasterContrller;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\WorktimelogsController;


use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BonusStudentController;
use App\Http\Controllers\CheckReportStdController;
use App\Http\Controllers\FormManageController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AzureTTSController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\PayrollController;
// use App\Http\Controllers\RouteToggleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/home3', [HomeController::class, 'home3']);
Route::post('/attendance/import', [AttendanceController::class, 'import']);

Route::post('/webhook/line', [LineWebhookController::class, 'Handle'])->name('webhook.line');


Route::group(['middleware' => 'check.opening.date.time'], function () {
    // Dynamic slug-based route for all forms
    Route::get('/form/{slug}', [ReceiptFormController::class, 'showFormBySlug'])->name('form.show');
    Route::get('/gelosea', [ReceiptFormController::class, 'gelosea'])->name('gelosea');
    Route::get('/spelling-bee-islo', [ReceiptFormController::class, 'spelling_bee_islo'])->name('spelling-bee-islo');
    Route::get('/stemco_science', [ReceiptFormController::class, 'stemco_science'])->name('stemco_science');
    Route::get('/advance_english_grammar', [ReceiptFormController::class, 'advance_english_grammar'])->name('advance_english_grammar');
    Route::get('/isocsea-imocsea-national-round-2025', [ReceiptFormController::class, 'isocsea_imocsea_national_round_2025'])->name('isocsea-imocsea-national-round-2025');
    Route::get('/isocsea-imocsea-national-round', [ReceiptFormController::class, 'isocsea_imocsea_national_round'])->name('isocsea-imocsea-national-round');

    // Testing route
    Route::get('/testing-form-manage', [FormManageController::class, 'testing_form_manage'])->name('testing-form-manage');
});

Route::group(['middleware' => ['auth', 'checkUserId']], function () {
    Route::resource('studentBonus', BonusStudentController::class);
    Route::get('/studentBonus/schpid/{id}', [BonusStudentController::class, 'schpid'])->name('bonusRec');

    Route::get('/rec-time-logs', [WorktimelogsController::class, 'index'])->name('timelogs');
    Route::get('/update-late-time', [RecordtimeController::class, 'update_late_time'])->name('update_late_time');
    Route::get('/search-report-month-time', [RecordtimeController::class, 'search_reportMonthTime'])->name('search_reportMonthTime');
    Route::get('/find-UserRecTime/{user_id}/{status}/{month}/{year}', [RecordtimeController::class, 'find_UserRecTime'])->name('find_UserRecTime');
    Route::get('/find-StatusRecTime/{status}/{month}/{year}', [RecordtimeController::class, 'find_statusRecTime'])->name('find_statusRecTime');
    Route::get('/report-month-time', [RecordtimeController::class, 'reportTime'])->name('reportTime');
    Route::get('/testrec', [RecordtimeController::class, 'testrec'])->name('testrec');
    Route::get('/report-month-student', [StdlogController::class, 'reportMonthStd'])->name('reportMonthStd');
    Route::get('/searchr_reportMonthStd', [StdlogController::class, 'searchr_reportMonthStd'])->name('searchr_reportMonthStd');
    Route::get('/report-year-student', [StdlogController::class, 'reportYearStd'])->name('reportYearStd');
    Route::POST('/saveCheck', [CheckReportStdController::class, 'store'])->name('saveCheck');
    Route::get('/getDataRec/{id}', [CheckReportStdController::class, 'DataRec'])->name('getDataRec');
    Route::get('/fetch-checkbox-states', [CheckReportStdController::class, 'fetchCheckboxStates'])->name('fetchCheckboxStates');

    // Route::get('/stdLog-search', [StdlogController::class, 'searchTestLog'])->name('stdlog.search');
    Route::get('/report-reciept-student', [StdlogController::class, 'reportRecieptStd'])->name('reportRecieptStd');
    Route::get('/report-reciept-student-search', [StdlogController::class, 'reportRecieptStdSearch'])->name('reportRecieptStdSearch');
    Route::get('/report-reciept-student/{id}', [StdlogController::class, 'reportRecieptStdShow'])->name('reportRecieptStdShow');

    // Payment Voucher Routes (ใบสำคัญจ่าย)
    Route::get('/debug-attendance', function () {
        $start_date = '2025-12-26';
        $end_date = '2026-01-25';
        $user_id = 16;
        $records = \App\Models\Recordtime::where('user_id', $user_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->where('late_time', '>', 0)
            ->orderBy('date')
            ->get(['date', 'late_time', 'explain']);

        return [
            'records' => $records,
            'sum' => $records->sum('late_time')
        ];
    });
});

Route::middleware(['auth'])->prefix('payment-voucher')->name('payment-voucher.')->group(function () {
    Route::get('/', [PaymentVoucherController::class, 'index'])->name('index');
    Route::get('/create', [PaymentVoucherController::class, 'create'])->name('create');
    Route::post('/store', [PaymentVoucherController::class, 'store'])->name('store');
    Route::get('/show/{id}', [PaymentVoucherController::class, 'show'])->name('show');
    Route::get('/edit/{id}', [PaymentVoucherController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PaymentVoucherController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PaymentVoucherController::class, 'destroy'])->name('destroy');

    // Force Delete (ลบถาวรพร้อมข้อมูลที่เกี่ยวข้องทั้งหมด)
    Route::delete('/force-destroy/{id}', [PaymentVoucherController::class, 'forceDestroy'])->name('force-destroy');

    // Approval Workflow
    Route::post('/review/{id}', [PaymentVoucherController::class, 'review'])->name('review');
    Route::post('/approve/{id}', [PaymentVoucherController::class, 'approve'])->name('approve');
    Route::post('/reject/{id}', [PaymentVoucherController::class, 'reject'])->name('reject');
    Route::post('/reject-to-draft/{id}', [PaymentVoucherController::class, 'rejectToDraft'])->name('reject-to-draft');
    Route::post('/complete/{id}', [PaymentVoucherController::class, 'complete'])->name('complete');

    // Lists
    Route::get('/pending', [PaymentVoucherController::class, 'pendingList'])->name('pending');
    Route::get('/approved', [PaymentVoucherController::class, 'approvedList'])->name('approved');

    // Export
    Route::get('/export/{id}', [PaymentVoucherController::class, 'exportExcel'])->name('export');
    Route::get('/print-pdf/{id}', [PaymentVoucherController::class, 'printPdf'])->name('print-pdf');

    // Attachments
    Route::post('/{id}/upload-attachment', [PaymentVoucherController::class, 'uploadAttachment'])->name('upload-attachment');
    Route::delete('/attachment/{id}', [PaymentVoucherController::class, 'deleteAttachment'])->name('delete-attachment');

    // Payee & Voucher Number APIs
    Route::get('/search-payee', [PaymentVoucherController::class, 'searchPayee'])->name('search-payee');
    Route::get('/next-voucher-no', [PaymentVoucherController::class, 'getNextVoucherNo'])->name('next-voucher-no');
    Route::post('/save-payee', [PaymentVoucherController::class, 'savePayee'])->name('save-payee');

    // Import Excel
    Route::get('/import', [PaymentVoucherController::class, 'showImportForm'])->name('import');
    Route::post('/import', [PaymentVoucherController::class, 'importExcel'])->name('import.process');
    Route::post('/import/store', [PaymentVoucherController::class, 'storeImport'])->name('import.store');
    Route::get('/import/template', [PaymentVoucherController::class, 'downloadTemplate'])->name('import.template');
});

// Bank Statement Routes
Route::middleware(['auth'])->prefix('bank-statement')->name('bank-statement.')->group(function () {
    Route::get('/', [App\Http\Controllers\BankStatementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\BankStatementController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\BankStatementController::class, 'store'])->name('store');
    Route::get('/import', [App\Http\Controllers\BankStatementController::class, 'showImport'])->name('import');
    Route::post('/import', [App\Http\Controllers\BankStatementController::class, 'import'])->name('import.process');
    Route::get('/sync', [App\Http\Controllers\BankStatementController::class, 'showSync'])->name('sync');
    Route::post('/sync', [App\Http\Controllers\BankStatementController::class, 'syncPayroll'])->name('sync.process');
    Route::delete('/{id}', [App\Http\Controllers\BankStatementController::class, 'destroy'])->name('destroy');
});

// Payroll Routes (ระบบเงินเดือน)
Route::middleware(['auth'])->prefix('payroll')->name('payroll.')->group(function () {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::get('/create', [PayrollController::class, 'create'])->name('create');
    Route::post('/store', [PayrollController::class, 'store'])->name('store');
    Route::get('/{id}', [PayrollController::class, 'show'])->name('show');
    Route::delete('/{id}', [PayrollController::class, 'destroy'])->name('destroy');

    // Generate payrolls for all employees
    Route::post('/{id}/generate', [PayrollController::class, 'generatePayrolls'])->name('generate');

    // Approval workflow
    Route::post('/{id}/approve', [PayrollController::class, 'approve'])->name('approve');
    Route::post('/{id}/paid', [PayrollController::class, 'markAsPaid'])->name('paid');

    // Individual payroll
    Route::get('/{period_id}/payroll/{payroll_id}', [PayrollController::class, 'editPayroll'])->name('edit-payroll');
    Route::put('/{period_id}/payroll/{payroll_id}', [PayrollController::class, 'updatePayroll'])->name('update-payroll');
    Route::delete('/{period_id}/payroll/{payroll_id}', [PayrollController::class, 'deletePayroll'])->name('delete-payroll');

    // Payslip
    Route::get('/{period_id}/payslip/{payroll_id}', [PayrollController::class, 'payslip'])->name('payslip');

    // Import Excel
    Route::get('/{id}/import', [PayrollController::class, 'showImport'])->name('import');
    Route::match(['GET', 'POST'], '/{id}/import/preview', [PayrollController::class, 'previewImport'])->name('import.preview');
    Route::post('/{id}/import/process', [PayrollController::class, 'processImport'])->name('import.process');

    // Bulk Edit
    Route::get('/{id}/bulk-edit', [PayrollController::class, 'bulkEdit'])->name('bulk-edit');
    Route::post('/{id}/bulk-update', [PayrollController::class, 'bulkUpdate'])->name('bulk-update');
    // Sync Attendance
    Route::post('/{id}/sync-attendance', [PayrollController::class, 'syncAttendance'])->name('sync-attendance');
});

// Role & Permission Management (super-admin only)
Route::group(['middleware' => ['auth', 'role:super-admin']], function () {
    Route::prefix('system-admin')->name('admin.')->group(function () {
        // Roles Management
        Route::resource('roles', RoleController::class);

        // Permissions Management
        Route::resource('permissions', PermissionController::class)->except(['show']);
        Route::post('/permissions/bulk-create', [PermissionController::class, 'bulkCreate'])->name('permissions.bulk-create');

        // User Roles Management
        Route::get('/user-roles', [UserRoleController::class, 'index'])->name('user-roles.index');
        Route::get('/user-roles/{id}/edit', [UserRoleController::class, 'edit'])->name('user-roles.edit');
        Route::put('/user-roles/{id}', [UserRoleController::class, 'update'])->name('user-roles.update');
    });
});

Route::get('/stdLog-search', [StdlogController::class, 'search_id'])->name('searchTestLog');


Route::group(['middleware' => ['auth', 'Godeye']], function () {
    Route::resource('Godeye', GodeyeContreoller::class);
});

Route::group(['middleware' => ['auth', 'switch']], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
});
Route::group(['middleware' => ['auth', 'outhome']], function () {

    Route::get('/updateStudentStatus', [App\Http\Controllers\HomeController::class, 'updateStudentStatus'])->name('updateStudentStatus');
    Route::resource('user-tags', App\Http\Controllers\UserTagController::class);
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/Home', [App\Http\Controllers\HomeController::class, 'home2'])->name('home2');
});


Route::get('/test-fn', [TestController::class, 'store'])->name('testFs')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]); // test

//Route::post('/test-fn/iclock/cdata', [TestController::class, 'store'])->name('testFn')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
//Route::post('/test-fn/iclock/getrequest', [TestController::class, 'store'])->name('testFnn')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::group(['middleware' => ['auth']], function () {
    Route::get('/search-rect', [RecordtimeController::class, 'search_rect'])->name('search_rect');

    Route::get('/recordtime/import', [RecordtimeController::class, 'ShowImportForm'])->name('recordtime.import');
    Route::post('/recordtime/import', [RecordtimeController::class, 'ProcessImport'])->name('recordtime.import.process');
    Route::post('/recordtime/update-fingerprint', [RecordtimeController::class, 'UpdateFingerprint'])->name('recordtime.update.fingerprint');

    Route::get('/company-holidays', [App\Http\Controllers\CompanyHolidayController::class, 'Index'])->name('company.holidays.index');
    Route::post('/company-holidays', [App\Http\Controllers\CompanyHolidayController::class, 'Store'])->name('company.holidays.store');
    Route::delete('/company-holidays/{id}', [App\Http\Controllers\CompanyHolidayController::class, 'Destroy'])->name('company.holidays.destroy');
    Route::post('/company-holidays/recalculate', [App\Http\Controllers\CompanyHolidayController::class, 'RecalculateAll'])->name('company.holidays.recalculate');
    Route::get('/company-holidays/compensate', [App\Http\Controllers\CompanyHolidayController::class, 'GetUserCompensateDays'])->name('company.holidays.compensate');
    Route::post('/company-holidays/import', [App\Http\Controllers\CompanyHolidayController::class, 'Import'])->name('company.holidays.import');
    Route::get('/company-holidays/template', [App\Http\Controllers\CompanyHolidayController::class, 'DownloadTemplate'])->name('company.holidays.template');

    Route::get('/leave-requests', [App\Http\Controllers\LeaveRequestController::class, 'Index'])->name('leave-requests.index');
    Route::get('/leave-requests/create', [App\Http\Controllers\LeaveRequestController::class, 'Create'])->name('leave-requests.create');
    Route::post('/leave-requests', [App\Http\Controllers\LeaveRequestController::class, 'Store'])->name('leave-requests.store');
    Route::get('/leave-requests/pending', [App\Http\Controllers\LeaveRequestController::class, 'PendingApprovals'])->name('leave-requests.pending');
    Route::get('/leave-requests/{id}', [App\Http\Controllers\LeaveRequestController::class, 'Show'])->name('leave-requests.show');
    Route::get('/leave-requests/{id}/edit', [App\Http\Controllers\LeaveRequestController::class, 'Edit'])->name('leave-requests.edit');
    Route::put('/leave-requests/{id}', [App\Http\Controllers\LeaveRequestController::class, 'Update'])->name('leave-requests.update');
    Route::get('/leave-requests/{id}/print', [App\Http\Controllers\LeaveRequestController::class, 'PrintLeave'])->name('leave-requests.print');
    Route::get('/leave-requests/{id}/export', [App\Http\Controllers\LeaveRequestController::class, 'ExportDocx'])->name('leave-requests.export');
    Route::get('/leave-requests/{id}/pdf', [App\Http\Controllers\LeaveRequestController::class, 'ExportPdf'])->name('leave-requests.pdf');
    Route::post('/leave-requests/{id}/approve', [App\Http\Controllers\LeaveRequestController::class, 'Approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{id}/reject', [App\Http\Controllers\LeaveRequestController::class, 'Reject'])->name('leave-requests.reject');
    Route::delete('/leave-requests/{id}', [App\Http\Controllers\LeaveRequestController::class, 'Destroy'])->name('leave-requests.destroy');
    Route::post('/user/signature', [App\Http\Controllers\LeaveRequestController::class, 'SaveSignature'])->name('user.signature.save');

    Route::get('/profile/signature', [App\Http\Controllers\ProfileController::class, 'Signature'])->name('profile.signature');
    Route::post('/profile/signature', [App\Http\Controllers\ProfileController::class, 'UpdateSignature'])->name('profile.signature.update');
    Route::delete('/profile/signature', [App\Http\Controllers\ProfileController::class, 'DeleteSignature'])->name('profile.signature.delete');
    Route::post('/profile/contact', [App\Http\Controllers\ProfileController::class, 'UpdateContact'])->name('profile.contact.update');
    Route::get('/profile/data', [App\Http\Controllers\ProfileController::class, 'GetProfileData'])->name('profile.data');
    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'UpdateProfile'])->name('profile.update');

    Route::resource('recordtime', RecordtimeController::class);
    Route::resource('app-down-load', AppDownloadController::class);
    // Route::delete('/app-down-load/{id}', AppDownloadController::class,'destroy')->name('app-down-load.delete');

    // Route::delete('/recordtime/{id}', [RecordtimeController::class, 'destroy'])->name('recordtimes.destroy');

    //settime
    Route::get('/report-future-student', [StdlogController::class, 'reportFutureStd'])->name('reportFutureStd');
    Route::post('/save_times', [TimeController::class, 'saveTimes'])->name('save_times');
    Route::get('/forms-time', [TimeController::class, 'formsTimes'])->name('formsTimes');
    Route::get('/forms-control', [TimeController::class, 'formsControl'])->name('formsControl');
    Route::delete('/delete_control/{id}', [TimeController::class, 'deleteControl'])->name('delete_control');
    //std log

    Route::get('/report-future-student', [StdlogController::class, 'reportFutureStd'])->name('reportFutureStd');
    Route::get('/searchFuture', [StdlogController::class, 'searchFuture'])->name('searchFuture');
    Route::get('/search_future', [StdlogController::class, 'search_future'])->name('search_future');
    Route::get('/report-student', [StdlogController::class, 'index'])->name('report-student');
    Route::get('/search_status', [StdlogController::class, 'search_status'])->name('search_status');
    route::get('/Super-Master', [SuperMasterContrller::class, 'index'])->name('sm');
    route::get('/testmodal', [SuperMasterContrller::class, 'testModal'])->name('test_modal');
    route::get('/testReq', [SuperMasterContrller::class, 'testReq'])->name('testReq');
    Route::get('/perform-retrospective-check', [RetrospectiveCheckController::class, 'performRetrospectiveCheck']);
    Route::resource('stdlog', StdlogController::class);
    Route::get('stdlog/create', [StdlogController::class, 'create'])->name('stdlog_create');

    //user

    Route::get('/users', [EmployeeController::class, 'index'])->name('users');
    Route::get('/users/create', [EmployeeController::class, 'create'])->name('users.create');
    Route::get('/users/show', [EmployeeController::class, 'show'])->name('users.show');
    Route::get('/users/edit/{id}', [EmployeeController::class, 'edit'])->name('users.edit');
    Route::put('/users/save/{id}', [EmployeeController::class, 'update'])->name('users.save');
    Route::put('/users/msave/{id}', [EmployeeController::class, 'mupdate'])->name('users.msave');
    Route::post('/users/store', [EmployeeController::class, 'store'])->name('users.store');
    Route::delete('/users/destroy/{id}', [EmployeeController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/destroy_all', [EmployeeController::class, 'destroy_all'])->name('users.destroy_all');
    Route::post('/users/toggle-disable/{id}', [EmployeeController::class, 'ToggleDisable'])->name('users.toggle-disable');

    //subject
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
    Route::get('/subjects/view/{id}', [SubjectController::class, 'view'])->name('subjects.view');
    Route::get('/subjects/edit/{id}', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::post('/subjects/select_list/{id}', [SubjectController::class, 'select_list'])->name('subjects.select_list');
    Route::put('/subjects/save/{id}', [SubjectController::class, 'update'])->name('subjects.save');
    Route::post('/subjects/store', [SubjectController::class, 'store'])->name('subjects.store');
    Route::delete('/subjects/destroy_all', [SubjectController::class, 'destroy_all'])->name('subjects.destroy_all');
    Route::delete('/subjects/destroy/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/subjects/viewstudent/', [SubjectController::class, 'overview'])->name('subjects.overview');
    Route::get('/subjects/viewstudent/searchsubSTD', [SubjectController::class, 'searchsubSTD'])->name('searchsubSTD');

    //subject price

    Route::get('/subjects_price', [SubjectPriceController::class, 'index'])->name('subjects_price');
    Route::get('/subjects_price/edit/{id}', [SubjectPriceController::class, 'edit'])->name('subjects_price.edit');
    Route::post('/subjects_price/select_list/{id}', [SubjectPriceController::class, 'select_list'])->name('subjects_price.select_list');
    Route::put('/subjects_price/save/{id}', [SubjectPriceController::class, 'update'])->name('subjects_price.save');
    Route::post('/subjects_price/store', [SubjectPriceController::class, 'store'])->name('subjects_price.store');
    Route::delete('/subjects_price/destroy_all', [SubjectPriceController::class, 'destroy_all'])->name('subjects_price.destroy_all');
    Route::delete('/subjects_price/destroy/{id}', [SubjectPriceController::class, 'destroy'])->name('subjects_price.destroy');

    //level
    Route::get('/levels', [LevelController::class, 'index'])->name('levels');
    Route::get('/levels/edit/{id}', [LevelController::class, 'edit'])->name('levels.edit');
    Route::post('/levels/select_list/{id}', [LevelController::class, 'select_list'])->name('levels.select_list');
    Route::put('/levels/save/{id}', [LevelController::class, 'update'])->name('levels.save');
    Route::post('/levels/store', [LevelController::class, 'store'])->name('levels.store');
    Route::delete('/levels/destroy_all', [LevelController::class, 'destroy_all'])->name('levels.destroy_all');
    Route::delete('/levels/destroy/{id}', [LevelController::class, 'destroy'])->name('levels.destroy');

    //student

    Route::get('/students', [StudentController::class, 'index'])->name('students');
    Route::get('/students/expire', [StudentController::class, 'expire'])->name('students.expire');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::get('/students/show/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/students/view/{id}', [StudentController::class, 'view'])->name('students.view');
    Route::get('/students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/save/{id}', [StudentController::class, 'update'])->name('students.save');
    Route::get('/students/show/history/{sub_id}/{std_id}', [StudentController::class, 'std_his'])->name('std_his');

    Route::post('/students/store', [StudentController::class, 'store'])->name('students.store');
    Route::post('/students/import', [StudentController::class, 'importExcel'])->name('students.import');
    Route::delete('/students/destroy/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::delete('/students/destroy_all', [StudentController::class, 'destroy_all'])->name('students.destroy_all');

    //parent

    Route::get('/parents', [ParentRegisterController::class, 'index'])->name('parents');
    Route::get('/parents/create', [ParentRegisterController::class, 'create'])->name('parents.create');
    Route::get('/parents/show', [ParentRegisterController::class, 'show'])->name('parents.show');
    Route::get('/parents/edit/{id}', [ParentRegisterController::class, 'edit'])->name('parents.edit');
    Route::put('/parents/save/{id}', [ParentRegisterController::class, 'update'])->name('parents.save');
    Route::post('/parents/store1', [ParentRegisterController::class, 'store1'])->name('parents.store1');
    Route::delete('/parents/destroy/{id}', [ParentRegisterController::class, 'destroy'])->name('parents.destroy');
    Route::delete('/parents/destroy_all', [ParentRegisterController::class, 'destroy_all'])->name('parents.destroy_all');

    //course

    Route::get('/course', [CourseController::class, 'index'])->name('course');
    Route::get('/course/create', [CourseController::class, 'create'])->name('course.create');
    Route::get('/course/show/{type}/{lang}/{id}', [CourseController::class, 'show'])->name('course.show');
    Route::any('/course/simulate/{type}/{lang}', [CourseController::class, 'simulate'])->name('course.simulate');
    Route::get('/course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');
    Route::put('/course/save/{id}', [CourseController::class, 'update'])->name('course.save');
    Route::post('/course/store', [CourseController::class, 'store'])->name('course.store');
    Route::delete('/course/destroy/{id}', [CourseController::class, 'destroy'])->name('course.destroy');
    Route::delete('/course/destroy_all', [CourseController::class, 'destroy_all'])->name('course.destroy_all');




    //reciept

    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts');
    Route::get('/receipts_all', [ReceiptController::class, 'index_all'])->name('receipts_all');
    Route::get('/receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
    Route::match(array('GET', 'POST'), '/receipts/print_day', [ReceiptController::class, 'print_day'])->name('receipts.print_day');
    Route::match(array('GET', 'POST'), '/receipts/print_month', [ReceiptController::class, 'print_month'])->name('receipts.print_month');
    Route::match(array('GET', 'POST'), '/receipts/print_field', [ReceiptController::class, 'print_field'])->name('receipts.print_field');
    Route::get('/receipts/show/{type}/{id}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::match(array('GET', 'POST'), '/receipts/show_day', [ReceiptController::class, 'show_day'])->name('receipts.show_day');
    Route::get('/receipts/edit/{id}/{type}', [ReceiptController::class, 'edit'])->name('receipts.edit');
    Route::get('/receipts/migrate/{id}', [ReceiptController::class, 'migrateReceipt'])->name('receipts.migrate');
    Route::put('/receipts/save/{id}', [ReceiptController::class, 'update'])->name('receipts.save');
    Route::post('/receipts/store', [ReceiptController::class, 'store'])->name('receipts.store');
    Route::post('/receipts/conv', [ReceiptController::class, 'conv'])->name('receipts.conv');
    Route::post('/receipts/import', [ReceiptController::class, 'importExcel'])->name('receipts.import');
    Route::delete('/receipts/destroy/{id}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
    Route::delete('/receipts/destroy_all', [ReceiptController::class, 'destroy_all'])->name('receipts.destroy_all');



    //history

    Route::get('/histories', [HistoryController::class, 'index'])->name('histories');
    Route::get('/histories/stdsub/{id}', [HistoryController::class, 'stdsub'])->name('stdsub');
    Route::get('/histories/stdsperiod/{id}/{sub_id}', [HistoryController::class, 'stdsperiod'])->name('stdsperiod');
    Route::get('/histories/edit/{id}', [HistoryController::class, 'edit'])->name('histories.edit');
    Route::put('/histories/save/{id}', [HistoryController::class, 'update'])->name('histories.save');
    Route::post('/histories/show', [HistoryController::class, 'show'])->name('histories.show');
    Route::post('/histories/store', [HistoryController::class, 'store'])->name('histories.store');
    Route::delete('/histories/destroy/{id}', [HistoryController::class, 'destroy'])->name('histories.destroy');
    Route::delete('/histories/destroy_all', [HistoryController::class, 'destroy_all'])->name('histories.destroy_all');
    Route::match(array('GET', 'POST'), '/histories/print_std', [HistoryController::class, 'print_std'])->name('histories.print_std');
    Route::match(array('GET', 'POST'), '/histories/print_day', [HistoryController::class, 'print_day'])->name('histories.print_day');

    //receipt_school

    Route::match(array('GET', 'POST'), '/receipt_school', [ReceiptSchoolController::class, 'index'])->name('receipt_school');
    Route::get('/receipt_school/{sub_id}', [ReceiptSchoolController::class, 'search'])->name('receipt_school.search');
    Route::get('/receipt_school/{sub_id}/{level_id}/{week}/{price}/{qty}/{discount}/{book_price}/{type}/{course_type}/{entrance_fee}', [ReceiptSchoolController::class, 'searchs'])->name('receipt_school.searchs');
    Route::get('/receipt_school/{sub_id}/{level_id}/{book}/{week}/{course_type}/', [ReceiptSchoolController::class, 'sumbook'])->name('receipt_school.sumbook');
    // Route::get('/receipt_school/{sub_id}/{level_id}/{book}/', [ReceiptSchoolController::class, 'sumbook'])->name('receipt_school.sumbook');
    Route::get('/receipt_school/find/{parent}', [ReceiptSchoolController::class, 'find'])->name('receipt_school.find');

    Route::post('/receipt_school/store', [ReceiptSchoolController::class, 'store'])->name('receipt_school.store');

    Route::get('/receipt_school/show/{type}/{id}', [ReceiptSchoolController::class, 'show'])->name('receipt_school.show');

    Route::get('/receipt_school/{id}/{type}', [ReceiptSchoolController::class, 'edit'])->name('receipt_school.edit');
    Route::put('/receipt_school/save/{id}', [ReceiptSchoolController::class, 'update'])->name('receipt_school.save');
    Route::post('/receipt_school/upload', [ReceiptSchoolController::class, 'fileStore'])->name('receipt_school.upload');
    Route::post('/receipt_school/upload/delete', [ReceiptSchoolController::class, 'fileDestroy'])->name('receipt_school.delete');
    Route::put('/receipt_school/delete/{id}/{id2}', [ReceiptSchoolController::class, 'deleteImg'])->name('receipt_school.deleteImg');
    Route::delete('/receipt_school/destroy/{id}', [ReceiptSchoolController::class, 'destroy'])->name('receipt_school.destroy');

    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs');
    Route::get('/admin/activity-logs/filter', [ActivityLogController::class, 'filter'])->name('logs.filter');
    Route::get('/admin/activity-logs/full-filter', [ActivityLogController::class, 'fullFilter'])->name('logs.full.filter');

    Route::get('/referent_url', [ReceiptFormController::class, 'referent_url'])->name('referent_url');
    Route::get('/generate_url', [ReceiptFormController::class, 'generateUrl'])->name('generate_url');
    Route::post('/generate_url', [ReceiptFormController::class, 'generateUrlSave'])->name('generate_url.save');
    Route::get('/referent_url/edit', [ReceiptFormController::class, 'referent_urlEdit'])->name('referent_url.edit');
    Route::post('/referent_url/edit', [ReceiptFormController::class, 'referent_urlEditSave'])->name('referent_url.edit.save');
    Route::delete('/referent_url/delete', [ReceiptFormController::class, 'referent_urlDelete'])->name('referent_url.delete');

    Route::get('/referent_code', [ReceiptFormController::class, 'referent_code'])->name('referent_code');
    Route::get('/generate_code', [ReceiptFormController::class, 'generate_code'])->name('generate_code');
    Route::post('/referent_code', [ReceiptFormController::class, 'referent_codeSave'])->name('referent_code.save');
    Route::get('/referent_code/edit', [ReceiptFormController::class, 'referent_codeEdit'])->name('referent_code.edit');
    Route::post('/referent_code/edit', [ReceiptFormController::class, 'referent_codeEditSave'])->name('referent_code.edit.save');
    Route::delete('/referent_code/delete', [ReceiptFormController::class, 'referent_codeDelete'])->name('referent_code.delete');

    //form manage
    // Route::resource('form-manage', FormManageController::class);
    Route::get('/form-manage', [FormManageController::class, 'index'])->name('form-manage.index');
    // form-manage.store
    Route::post('/form-manage/store', [FormManageController::class, 'store'])->name('form-manage.store');
    // form-manage.view
    Route::get('/form-manage/{id}', [FormManageController::class, 'view'])->name('form-manage.view');
    //destroy
    Route::post('/form-manage/destroy', [FormManageController::class, 'destroy'])->name('form-manage.destroy');
    Route::get('/select_route', [FormManageController::class, 'selectRoute'])->name('select_route');
    // Route::post('/create-form', [FormManageController::class, 'create'])->name('create-form');
    Route::match(['GET', 'POST'], '/create-form', [FormManageController::class, 'create'])->name('create-form');
    Route::get('/form-manage/{id}', [FormManageController::class, 'view'])->name('form-manage.view');
    Route::post('/edit-option-grade', [FormManageController::class, 'editOptionGrade'])->name('edit-option-grade');
    Route::post('/delete-option-grade', [FormManageController::class, 'deleteOptionGrade'])->name('delete-option-grade');

    Route::post('/edit-option-package', [FormManageController::class, 'editOptionPackage'])->name('edit-option-package');
    Route::post('/delete-option-package', [FormManageController::class, 'deleteOptionPackage'])->name('delete-option-package');
});

//form


Route::get('/cambridge/line', [ReceiptFormController::class, 'cambridge'])->name('cambridge.line');
Route::post('/form/upload', [ReceiptFormController::class, 'fileStore'])->name('form.upload');
Route::post('/form/upload/api', [ReceiptFormController::class, 'fileStoreAPI'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->name('form.upload.api');
Route::post('/form/upload/delete', [ReceiptFormController::class, 'fileDestroy'])->name('form.delete');
Route::post('/form/store', [ReceiptFormController::class, 'store'])->name('form.store');
Route::post('/form/storeFormManage', [ReceiptFormController::class, 'storeFormManage'])->name('form.storeFormManage');
Route::get('/form/complete/{sfield}', [ReceiptFormController::class, 'show'])->name('form.complete');
Route::get('/form/completed/{sfield}', [ReceiptFormController::class, 'showFormManage'])->name('form.completed');
Route::get('/form/complete1', [ReceiptFormController::class, 'show1'])->name('form.complete1');

Route::post('/form/store1', [ReceiptFormController::class, 'store1'])->name('form.store1');

Route::get('/receipts_form', [ReceiptFormController::class, 'index'])->name('receipts_form');
Route::get('/receipts_form_all', [ReceiptFormController::class, 'index_all'])->name('receipts_form_all');
Route::get('/receipts_form/edit/{id}', [ReceiptFormController::class, 'edit'])->name('receipts_form.edit');
Route::delete('/receipts_form/destroy/{id}', [ReceiptFormController::class, 'destroy'])->name('receipts_form.destroy');
Route::delete('/receipts_form/destroy_all', [ReceiptFormController::class, 'destroy_all'])->name('receipts_form.destroy_all');
Route::delete('/receipts_form/export', [ReceiptFormController::class, 'exportForm'])->name('receipts_form.export');
Route::post('/receipts_form/store', [ReceiptFormController::class, 'create'])->name('receipts_form.store');
Route::post('/receipts_form/update', [ReceiptFormController::class, 'update'])->name('receipts_form.update');
Route::post('/receipts_form/resend-mail/{id}', [ReceiptFormController::class, 'reSendMail'])->name('receipts_form.resend_mail');

Route::group(array('domain' => 'form.infinitebrain.co.th'), function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
});
Route::get('/send-line-message', [LineController::class, 'sendMessage'])->name('send.line.message');
Route::get('/test-view', [LineController::class, 'testView'])->name('line.testView');
// Route::get('/generate-tts', [LineController::class, 'generateTTS'])->name('line.generate-tts');


Route::post('/generate-tts', [AzureTTSController::class, 'generate'])->name('generate.tts');

Route::post('/webhook', [LineController::class, 'webhook'])->name('line.webhook');

// Route::get('/webhook', function () {
//     return response('Webhook verified', 200);
// });

//Route::get('/', [EmployeeController::class, 'index']);

Auth::routes([
    'register' => true, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

//Route::get('dashboard', [CustomAuthController::class, 'dashboard']);
Route::get('login', [CustomAuthController::class, 'index'])->name('login');
Route::post('custom-login', [CustomAuthController::class, 'customLogin'])->name('login.custom');
Route::get('registration', [CustomAuthController::class, 'registration'])->name('register-user');
Route::post('custom-registration', [CustomAuthController::class, 'customRegistration'])->name('register.custom');
Route::get('signout', [CustomAuthController::class, 'signOut'])->name('signout');

Route::get('/parent-register', [ParentRegisterController::class, 'register'])->name('parentregister');
Route::post('/parent-register/store', [ParentRegisterController::class, 'store'])->name('parentregister.store');
Route::get('/parent-register/complete', [ParentRegisterController::class, 'show'])->name('parentregister.complete');


//Route for normal user//
//Route::group(['middleware' => ['auth']], function () {
//   Route::get('/home', [App\Http\Controllers\CourseController::class, 'index'])->name('home');
//});

//Route for admin
/*Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['admin']], function () {
        Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index']);
    });
});*/

// Route::group(['middleware' => 'inactivity'], function () {
//     // Routes that require inactivity timeout check

// });


// Route::get('/search_status/{month}/{year}', [StdlogController::class, 'search_status'])->name('search_status');





Route::get('/school', [SchoolController::class, 'index'])->name('school');



// Route::prefix('google')->name('google.')->group( function(){
//     Route::get('login', [GoogleController::class, 'loginWithGoogle'])->name('login');
//     Route::any('callback', [GoogleController::class, 'callbackFromGoogle'])->name('callback');
// });
Route::get('/login/google', [GoogleController::class, 'loginWithGoogle'])->name('google.login');
Route::get('/login/google/callback', [GoogleController::class, 'callbackFromGoogle'])->name('google.callback');

//Auth::routes();
Route::get('/login/parent', [LoginController::class, 'showParentLoginForm'])->name('login.parent');
Route::post('/login/parent', [LoginController::class, 'parentLogin']);
Route::get('/login/parent/logout', [LoginController::class, 'ParentsignOut'])->name('parent.logout');


// Route::view('/home', 'home')->middleware('auth');
////Route::view('/parent', 'parent')->middleware('auth');
Route::get('/parent', [SchoolController::class, 'index'])->name('parent');
Route::group(array('domain' => 'school.infinitebrain.co.th'), function () {
    Route::get('/', [App\Http\Controllers\SchoolController::class, 'index']);
});

Route::post('/parent/save', [SchoolController::class, 'update'])->name('parent.save');
Route::post('/parent/saveparent', [SchoolController::class, 'updateParent'])->name('parent.saveparent');

// Route::put('/students/save/{id}', [StudentController::class, 'update'])->name('students.save');

Route::get('/comment', [CommentController::class, 'index'])->name('comment');
Route::get('/comment/edit/{id}', [CommentController::class, 'edit'])->name('comment.edit');
Route::post('/comment/store1', [CommentController::class, 'store1'])->name('comment.store1');
Route::put('/comment/save/{id}', [CommentController::class, 'update'])->name('comment.save');
Route::post('/comment/upload', [CommentController::class, 'fileStore'])->name('comment.upload');
Route::post('/comment/upload/delete', [CommentController::class, 'fileDestroy'])->name('comment.delete');
Route::delete('/comment/destroy/{id}', [CommentController::class, 'destroy'])->name('comment.destroy');
Route::delete('/comment/destroy_all', [CommentController::class, 'destroy_all'])->name('comment.destroy_all');

Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

// Temporary Debug Route
Route::get('/debug-attendance', function () {
    $start_date = '2025-12-26';
    $end_date = '2026-01-25';
    $user_id = 16;
    $records = \App\Models\Recordtime::where('user_id', $user_id)
        ->whereBetween('date', [$start_date, $end_date])
        ->where('late_time', '>', 0)
        ->orderBy('date')
        ->get(['date', 'late_time', 'explain']);

    return [
        'records' => $records,
        'sum' => $records->sum('late_time')
    ];
});
