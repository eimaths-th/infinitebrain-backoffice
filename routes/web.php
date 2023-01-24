<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\EmployeeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\ReceiptController;

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


//Route for normal user
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
Route::get('/receipts/show/{type}/{id}', [ReceiptController::class, 'show'])->name('receipts.show');
Route::get('/receipts/edit/{id}', [ReceiptController::class, 'edit'])->name('receipts.edit');
Route::put('/receipts/save/{id}', [ReceiptController::class, 'update'])->name('receipts.save');
Route::post('/receipts/store', [ReceiptController::class, 'store'])->name('receipts.store');
Route::post('/receipts/import', [ReceiptController::class, 'importExcel'])->name('receipts.import');
Route::delete('/receipts/destroy/{id}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
Route::delete('/receipts/destroy_all', [ReceiptController::class, 'destroy_all'])->name('receipts.destroy_all');
