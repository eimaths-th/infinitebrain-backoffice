<?php

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
use App\Http\Controllers\ReceiptSchoolController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\RetrospectiveCheckController;
use App\Http\Controllers\StdlogController;
use App\Http\Controllers\SuperMasterContrller;

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

Route::group(array('domain' => 'form.infinitebrain.co.th'), function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
});

Route::group(array('domain' => 'school.infinitebrain.co.th'), function () {
    Route::get('/', [App\Http\Controllers\SchoolController::class, 'index']);
});

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
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

Route::get('/report-month-student', [StdlogController::class, 'reportMonthStd'])->name('reportMonthStd');
Route::get('/report-future-student', [StdlogController::class, 'reportFutureStd'])->name('reportFutureStd');
Route::get('/searchFuture', [StdlogController::class, 'searchFuture'])->name('searchFuture');
Route::get('/search_future', [StdlogController::class, 'search_future'])->name('search_future');
Route::get('/report-student', [StdlogController::class, 'index'])->name('report-student');
Route::get('/search_status', [StdlogController::class, 'search_status'])->name('search_status');
Route::get('/searchr_reportMonthStd', [StdlogController::class, 'searchr_reportMonthStd'])->name('searchr_reportMonthStd');
route::get('/Super-Master', [SuperMasterContrller::class, 'index'])->name('sm');
route::get('/testmodal', [SuperMasterContrller::class, 'testModal'])->name('test_modal');
Route::get('/perform-retrospective-check', [RetrospectiveCheckController::class, 'performRetrospectiveCheck']);
Route::resource('stdlog', 'StdlogController');
Route::get('stdlog/create', [StdlogController::class, 'create'])->name('stdlog_create');
// Route::get('/search_status/{month}/{year}', [StdlogController::class, 'search_status'])->name('search_status');

Route::get('/users', [EmployeeController::class, 'index'])->name('users');
Route::get('/users/create', [EmployeeController::class, 'create'])->name('users.create');
Route::get('/users/show', [EmployeeController::class, 'show'])->name('users.show');
Route::get('/users/edit/{id}', [EmployeeController::class, 'edit'])->name('users.edit');
Route::put('/users/save/{id}', [EmployeeController::class, 'update'])->name('users.save');
Route::put('/users/msave/{id}', [EmployeeController::class, 'mupdate'])->name('users.msave');
Route::post('/users/store', [EmployeeController::class, 'store'])->name('users.store');
Route::delete('/users/destroy/{id}', [EmployeeController::class, 'destroy'])->name('users.destroy');
Route::delete('/users/destroy_all', [EmployeeController::class, 'destroy_all'])->name('users.destroy_all');

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

Route::get('/subjects_price', [SubjectPriceController::class, 'index'])->name('subjects_price');
Route::get('/subjects_price/edit/{id}', [SubjectPriceController::class, 'edit'])->name('subjects_price.edit');
Route::post('/subjects_price/select_list/{id}', [SubjectPriceController::class, 'select_list'])->name('subjects_price.select_list');
Route::put('/subjects_price/save/{id}', [SubjectPriceController::class, 'update'])->name('subjects_price.save');
Route::post('/subjects_price/store', [SubjectPriceController::class, 'store'])->name('subjects_price.store');
Route::delete('/subjects_price/destroy_all', [SubjectPriceController::class, 'destroy_all'])->name('subjects_price.destroy_all');
Route::delete('/subjects_price/destroy/{id}', [SubjectPriceController::class, 'destroy'])->name('subjects_price.destroy');

Route::get('/levels', [LevelController::class, 'index'])->name('levels');
Route::get('/levels/edit/{id}', [LevelController::class, 'edit'])->name('levels.edit');
Route::post('/levels/select_list/{id}', [LevelController::class, 'select_list'])->name('levels.select_list');
Route::put('/levels/save/{id}', [LevelController::class, 'update'])->name('levels.save');
Route::post('/levels/store', [LevelController::class, 'store'])->name('levels.store');
Route::delete('/levels/destroy_all', [LevelController::class, 'destroy_all'])->name('levels.destroy_all');
Route::delete('/levels/destroy/{id}', [LevelController::class, 'destroy'])->name('levels.destroy');

Route::get('/students', [StudentController::class, 'index'])->name('students');
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

Route::get('/parents', [ParentRegisterController::class, 'index'])->name('parents');
Route::get('/parents/create', [ParentRegisterController::class, 'create'])->name('parents.create');
Route::get('/parents/show', [ParentRegisterController::class, 'show'])->name('parents.show');
Route::get('/parents/edit/{id}', [ParentRegisterController::class, 'edit'])->name('parents.edit');
Route::put('/parents/save/{id}', [ParentRegisterController::class, 'update'])->name('parents.save');
Route::post('/parents/store1', [ParentRegisterController::class, 'store1'])->name('parents.store1');
Route::delete('/parents/destroy/{id}', [ParentRegisterController::class, 'destroy'])->name('parents.destroy');
Route::delete('/parents/destroy_all', [ParentRegisterController::class, 'destroy_all'])->name('parents.destroy_all');

Route::get('/course', [CourseController::class, 'index'])->name('course');
Route::get('/course/create', [CourseController::class, 'create'])->name('course.create');
Route::get('/course/show/{type}/{lang}/{id}', [CourseController::class, 'show'])->name('course.show');
Route::any('/course/simulate/{type}/{lang}', [CourseController::class, 'simulate'])->name('course.simulate');
Route::get('/course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');
Route::put('/course/save/{id}', [CourseController::class, 'update'])->name('course.save');
Route::post('/course/store', [CourseController::class, 'store'])->name('course.store');
Route::delete('/course/destroy/{id}', [CourseController::class, 'destroy'])->name('course.destroy');
Route::delete('/course/destroy_all', [CourseController::class, 'destroy_all'])->name('course.destroy_all');

Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts');
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

Route::get('/parent-register', [ParentRegisterController::class, 'register'])->name('parentregister');
Route::post('/parent-register/store', [ParentRegisterController::class, 'store'])->name('parentregister.store');
Route::get('/parent-register/complete', [ParentRegisterController::class, 'show'])->name('parentregister.complete');
Route::get('/school', [SchoolController::class, 'index'])->name('school');

// Route::get('/gmec', [ReceiptFormController::class, 'gmec'])->name('gmec');
//Route::get('/olpcs', [ReceiptFormController::class, 'olpcs'])->name('olpcs');
// Route::get('/asiarope', [ReceiptFormController::class, 'asiarope'])->name('asiarope');
// Route::get('/gelosea', [ReceiptFormController::class, 'gelosea'])->name('gelosea');

Route::get('/imocsea', [ReceiptFormController::class, 'imocsea'])->name('imocsea');
Route::get('/isocsea', [ReceiptFormController::class, 'isocsea'])->name('isocsea');
Route::get('/imocsea+isocsea', [ReceiptFormController::class, 'imocsea_isocsea'])->name('imocsea+isocsea');

Route::get('/cambridge', [ReceiptFormController::class, 'cambridge'])->name('cambridge');
Route::get('/cambridge/line', [ReceiptFormController::class, 'cambridge'])->name('cambridge.line');
Route::post('/form/upload', [ReceiptFormController::class, 'fileStore'])->name('form.upload');
Route::post('/form/upload/api', [ReceiptFormController::class, 'fileStoreAPI'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->name('form.upload.api');
Route::post('/form/upload/delete', [ReceiptFormController::class, 'fileDestroy'])->name('form.delete');
Route::post('/form/store', [ReceiptFormController::class, 'store'])->name('form.store');
Route::get('/form/complete/{sfield}', [ReceiptFormController::class, 'show'])->name('form.complete');
Route::get('/form/complete1', [ReceiptFormController::class, 'show1'])->name('form.complete1');

Route::post('/form/store1', [ReceiptFormController::class, 'store1'])->name('form.store1');

Route::get('/receipts_form', [ReceiptFormController::class, 'index'])->name('receipts_form');
Route::get('/receipts_form/edit/{id}', [ReceiptFormController::class, 'edit'])->name('receipts_form.edit');
Route::delete('/receipts_form/destroy/{id}', [ReceiptFormController::class, 'destroy'])->name('receipts_form.destroy');
Route::delete('/receipts_form/destroy_all', [ReceiptFormController::class, 'destroy_all'])->name('receipts_form.destroy_all');
Route::delete('/receipts_form/export', [ReceiptFormController::class, 'exportForm'])->name('receipts_form.export');
Route::post('/receipts_form/store', [ReceiptFormController::class, 'create'])->name('receipts_form.store');
Route::post('/receipts_form/update', [ReceiptFormController::class, 'update'])->name('receipts_form.update');

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


Route::get('/histories', [HistoryController::class, 'index'])->name('histories');
Route::get('/histories/stdsub/{id}', [HistoryController::class, 'stdsub'])->name('stdsub');
Route::get('/histories/edit/{id}', [HistoryController::class, 'edit'])->name('histories.edit');
Route::put('/histories/save/{id}', [HistoryController::class, 'update'])->name('histories.save');
Route::post('/histories/show', [HistoryController::class, 'show'])->name('histories.show');
Route::post('/histories/store', [HistoryController::class, 'store'])->name('histories.store');
Route::delete('/histories/destroy/{id}', [HistoryController::class, 'destroy'])->name('histories.destroy');
Route::delete('/histories/destroy_all', [HistoryController::class, 'destroy_all'])->name('histories.destroy_all');
Route::match(array('GET', 'POST'), '/histories/print_std', [HistoryController::class, 'print_std'])->name('histories.print_std');
Route::match(array('GET', 'POST'), '/histories/print_day', [HistoryController::class, 'print_day'])->name('histories.print_day');

Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

Route::match(array('GET', 'POST'), '/receipt_school', [ReceiptSchoolController::class, 'index'])->name('receipt_school');
Route::get('/receipt_school/{sub_id}', [ReceiptSchoolController::class, 'search'])->name('receipt_school.search');
Route::get('/receipt_school/{sub_id}/{level_id}/{week}/{price}/{qty}/{discount}/{book_price}/{type}/{course_type}/{entrance_fee}', [ReceiptSchoolController::class, 'searchs'])->name('receipt_school.searchs');
Route::get('/receipt_school/{sub_id}/{level_id}/{book}/', [ReceiptSchoolController::class, 'sumbook'])->name('receipt_school.sumbook');
Route::get('/receipt_school/find/{parent}', [ReceiptSchoolController::class, 'find'])->name('receipt_school.find');

Route::post('/receipt_school/store', [ReceiptSchoolController::class, 'store'])->name('receipt_school.store');
Route::get('/receipt_school/receipt/show/{type}/{id}', [ReceiptSchoolController::class, 'show'])->name('receipt_school.show');
Route::get('/receipt_school/{id}/{type}', [ReceiptSchoolController::class, 'edit'])->name('receipt_school.edit');
Route::put('/receipt_school/save/{id}', [ReceiptSchoolController::class, 'update'])->name('receipt_school.save');
Route::post('/receipt_school/upload', [ReceiptSchoolController::class, 'fileStore'])->name('receipt_school.upload');
Route::post('/receipt_school/upload/delete', [ReceiptSchoolController::class, 'fileDestroy'])->name('receipt_school.delete');
Route::put('/receipt_school/delete/{id}/{id2}', [ReceiptSchoolController::class, 'deleteImg'])->name('receipt_school.deleteImg');
