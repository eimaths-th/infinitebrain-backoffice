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

Route::group(array('domain' => 'form.infinitebrain.co.th'), function()
{
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

});

Route::group(array('domain' => 'school.infinitebrain.co.th'), function()
{
    Route::get('/', [SchoolController::class, 'index']);

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
Route::get('/subjects/edit/{id}', [SubjectController::class, 'edit'])->name('subjects.edit');
Route::post('/subjects/select_list/{id}', [SubjectController::class, 'select_list'])->name('subjects.select_list');
Route::put('/subjects/save/{id}', [SubjectController::class, 'update'])->name('subjects.save');
Route::post('/subjects/store', [SubjectController::class, 'store'])->name('subjects.store');
Route::delete('/subjects/destroy_all', [SubjectController::class, 'destroy_all'])->name('subjects.destroy_all');
Route::delete('/subjects/destroy/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

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
Route::get('/students/show', [StudentController::class, 'show'])->name('students.show');
Route::get('/students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/students/save/{id}', [StudentController::class, 'update'])->name('students.save');
Route::post('/students/store', [StudentController::class, 'store'])->name('students.store');
Route::delete('/students/destroy/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
Route::delete('/students/destroy_all', [StudentController::class, 'destroy_all'])->name('students.destroy_all');


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
Route::get('/receipts/show/{type}/{id}', [ReceiptController::class, 'show'])->name('receipts.show');
Route::get('/receipts/edit/{id}', [ReceiptController::class, 'edit'])->name('receipts.edit');
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


Route::get('/gmec', [ReceiptFormController::class, 'gmec'])->name('gmec');
Route::get('/olpcs', [ReceiptFormController::class, 'olpcs'])->name('olpcs');
Route::post('/form/upload', [ReceiptFormController::class, 'fileStore'])->name('form.upload');
Route::post('/form/upload/delete', [ReceiptFormController::class, 'fileDestroy'])->name('form.delete');
Route::post('/form/store', [ReceiptFormController::class, 'store'])->name('form.store');
Route::get('/form/complete', [ReceiptFormController::class, 'show'])->name('form.complete');
Route::get('/form/complete1', [ReceiptFormController::class, 'show1'])->name('form.complete1');

Route::post('/form/store1', [ReceiptFormController::class, 'store1'])->name('form.store1');



Route::get('/receipts_form', [ReceiptFormController::class, 'index'])->name('receipts_form');
Route::get('/receipts_form/edit/{id}', [ReceiptFormController::class, 'edit'])->name('receipts_form.edit');
Route::delete('/receipts_form/destroy/{id}', [ReceiptFormController::class, 'destroy'])->name('receipts_form.destroy');
Route::delete('/receipts_form/destroy_all', [ReceiptFormController::class, 'destroy_all'])->name('receipts_form.destroy_all');
Route::post('/receipts_form/store', [ReceiptFormController::class, 'create'])->name('receipts_form.store');

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

