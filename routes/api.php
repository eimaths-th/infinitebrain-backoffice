<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth.apikey')->group(function () {
    Route::get('/searchstudent', [APIController::class, 'searchStudent']);
    Route::get('/searchsubject', [APIController::class, 'searchSubject']);
    Route::post('/savehistories', [APIController::class, 'saveHistories'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// Route::get('/test-fn', [TestController::class, 'store'])->name('testFs')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);// test
Route::post('/test-fn/iclock/cdata', [TestController::class, 'store'])->name('testFn')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/test-fn/iclock/getrequest', [TestController::class, 'store'])->name('testFnn')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
