<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\DailyIncomeController;

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

    // Leave Request API

});

Route::get('/leave-requests/pending', [APIController::class, 'getPendingLeaveRequests']);
Route::get('/check-holiday', [APIController::class, 'checkHoliday']);

// Daily Income Summary API (สำหรับ n8n LINE Notification)
Route::prefix('income')->group(function () {
    Route::get('/daily', [DailyIncomeController::class, 'dailySummary']);        // ?date=2026-01-06
    Route::get('/today', [DailyIncomeController::class, 'todaySummary']);         // รายได้วันนี้
    Route::get('/yesterday', [DailyIncomeController::class, 'yesterdaySummary']); // รายได้เมื่อวาน
    Route::get('/weekly', [DailyIncomeController::class, 'weeklySummary']);       // ?start_date=&end_date=
    Route::get('/monthly', [DailyIncomeController::class, 'monthlySummary']);     // ?year=2026&month=1
    Route::get('/simulation-test', [DailyIncomeController::class, 'simulationTest']); // Test 3 days

});

// Route::get('/test-fn', [TestController::class, 'store'])->name('testFs')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);// test
