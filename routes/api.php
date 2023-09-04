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
Route::get('get-static-page', [CustomerController::class,'staticPage'])->name('get-static-page');

Route::get('get-category-list', [CustomerController::class,'getCategoryList'])->name('get-category-list');
Route::get('get-video-list/{id}', [CustomerController::class,'getVideoList'])->name('get-video-list');
Route::get('get-featured-video-list', [CustomerController::class,'getFeaturedList'])->name('get-featured-list');

Route::middleware('auth:api')->group(function () {
    Route::get('get-user-profile', [CustomerController::class,'getProfile'])->name('get-user-profile');
    Route::post('update-profile', [CustomerController::class,'updateProfile'])->name('update-profile');
    Route::post('update-password', [CustomerController::class,'updatePassword'])->name('update-password');

    Route::get('get-notification-list', [CustomerController::class,'notificationList'])->name('get-notification-list');
    Route::get('notification-read', [CustomerController::class,'notificationRead'])->name('notification-read');
    Route::get('notification-setting', [CustomerController::class,'notificationSetting'])->name('notification-setting');
    
    Route::get('get-bookmark-list', [CustomerController::class,'getBookmarkList'])->name('get-bookmark-list');
    Route::post('add-to-bookmark', [CustomerController::class,'addToBookmark'])->name('add-to-bookmark');
    
    Route::post('create-playlist', [CustomerController::class,'createPlayList'])->name('get-play-list');
    Route::get('get-playlist', [CustomerController::class,'getPlayList'])->name('get-play-list');
    Route::post('add-to-playlist', [CustomerController::class,'addToPlayList'])->name('add-to-playlist');
    Route::post('playlist-detail', [CustomerController::class,'PlayListDetail'])->name('playlist-detail');
    Route::post('delete-playlist', [CustomerController::class,'deletePlayList'])->name('delete-playlist');
    
    Route::get('get-category-name', [CustomerController::class,'getCategoryName'])->name('get-category-name');
    Route::get('get-video-name/{id}', [CustomerController::class,'getVideoName'])->name('get-video-name');
    Route::post('get-statistics', [CustomerController::class,'getStatistics'])->name('get-statistics');
    Route::post('search-video', [CustomerController::class,'searchVideo'])->name('search-video');
    Route::get('play-video/{id}', [CustomerController::class,'playVideo'])->name('play-video');
    
    Route::post('store-watched-video-duration', [CustomerController::class,'storeWatchedVideoDuration'])->name('store-watched-video-duration');

    Route::post('contact-support', [CustomerController::class,'contactSupport'])->name('contact-support');
    Route::get('contact-support-list', [CustomerController::class,'contactSupportList'])->name('contact-support-list');
    Route::post('contact-support-detail', [CustomerController::class,'contactSupportDetail'])->name('contact-support-detail');
    
    Route::get('log-out', [CustomerController::class,'logout'])->name('log-out');
});
