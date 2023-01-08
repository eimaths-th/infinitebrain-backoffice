<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\EmployeeController;
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

//Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/', [EmployeeController::class, 'index']);

Auth::routes([
    'register' => true, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);




//Route for normal user
Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});
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
Route::post('/users/store', [EmployeeController::class, 'store'])->name('users.store');
Route::delete('/users/destroy/{id}', [EmployeeController::class, 'destroy'])->name('users.destroy');
Route::delete('/users/destroy_all', [EmployeeController::class, 'destroy_all'])->name('users.destroy_all');
