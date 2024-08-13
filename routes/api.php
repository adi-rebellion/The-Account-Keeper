<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->group(function () {

    Route::get('profile', [LoginController::class, 'profile'])->name('profile');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::post('transaction', [TransactionController::class, 'makeTransaction'])->name('makeTransaction');


    Route::post('daily-closing-bal', [TransactionController::class, 'dailyClosingBalance'])->name('dailyClosingBalance');
    Route::post('average-bal', [TransactionController::class, 'averageBalance'])->name('averageBalance');
    Route::post('average-segment-bal', [TransactionController::class, 'averageSegmentBalance'])->name('averageSegmentBalance');
    Route::post('last-n-days-income', [TransactionController::class, 'lastNDaysIncome'])->name('lastNDaysIncome');
    Route::post('debit-trans-count', [TransactionController::class, 'debitTransactionCountLastNDays'])->name('debitTransactionCountLastNDays');
    Route::post('income-over-n', [TransactionController::class, 'incomeOverN'])->name('incomeOverN');

});

Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::post('login', [LoginController::class, 'login'])->name('login');
