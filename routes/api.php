<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;

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

Route::post('send-otp', [AuthController::class, 'sendOtp'])->name('send-otp');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get('get-user-profile', [CustomerController::class,'getProfile'])->name('get-user-profile');
    Route::post('update-profile', [CustomerController::class,'updateProfile'])->name('update-profile');
    Route::post('update-password', [CustomerController::class,'updatePassword'])->name('update-password');
    Route::get('get-static-page', [CustomerController::class,'staticPage'])->name('get-static-page');
    Route::get('notification-setting', [CustomerController::class,'notificationSetting'])->name('notification-setting');
    Route::get('log-out', [CustomerController::class,'logout'])->name('log-out');

    Route::post('contact-support', [CustomerController::class,'contactSupport'])->name('contact-support');
    Route::get('contact-support-list', [CustomerController::class,'contactSupportList'])->name('contact-support-list');
    // Route::post('contact-support-detail', [CustomerController::class,'contactSupportDetail'])->name('contact-support-detail');

});
