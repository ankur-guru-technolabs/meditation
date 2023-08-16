<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [LoginController::class, 'showLoginForm'])->name('/');
Route::post('/login-admin', [LoginController::class, 'login'])->name('login-admin');


Route::middleware(['admin'])->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

     
    Route::group(['prefix' => 'users','as'=>'users.'], function () {
        Route::get('list', [UserController::class, 'list'])->name('list');
        Route::get('details/{id}', [UserController::class, 'detail'])->name('detail');
        Route::post('status/update', [UserController::class, 'updateStatus'])->name('status-update');
    });
    
    Route::group(['prefix' => 'category','as'=>'category.'], function () {
        Route::get('list', [AdminController::class, 'categoryList'])->name('list');
        Route::get('add', [AdminController::class, 'categoryAdd'])->name('add');
        Route::post('store', [AdminController::class, 'categoryStore'])->name('store');
        Route::get('edit/{id}', [AdminController::class, 'categoryEdit'])->name('edit');
        Route::post('update', [AdminController::class, 'categoryUpdate'])->name('update');
        Route::get('delete/{id}', [AdminController::class, 'categoryDelete'])->name('delete');
    });
   
    Route::group(['prefix' => 'video','as'=>'video.'], function () {
        Route::get('list', [AdminController::class, 'videoList'])->name('list');
        Route::get('add', [AdminController::class, 'videoAdd'])->name('add');
        Route::post('store', [AdminController::class, 'videoStore'])->name('store');
        Route::get('edit/{id}', [AdminController::class, 'videoEdit'])->name('edit');
        Route::post('update', [AdminController::class, 'videoUpdate'])->name('update');
        Route::get('delete/{id}', [AdminController::class, 'videoDelete'])->name('delete');
        Route::post('featured/update', [AdminController::class, 'updatefeatured'])->name('featured-update');
    });

    Route::group(['prefix' => 'feedback','as'=>'feedback.'], function () {
        Route::get('list', [AdminController::class, 'feedbackList'])->name('list');
    });
    
    Route::group(['prefix' => 'static-pages','as'=>'static-pages.'], function () {
        Route::get('list', [AdminController::class, 'staticPagesList'])->name('list');
        Route::get('page-edit/{id}', [AdminController::class, 'pageEdit'])->name('page-edit');
        Route::post('page-update', [AdminController::class, 'pageUpdate'])->name('page-update');
    });
});

Route::post('/logout', function () {
    Auth::logout();

    Session::forget('session_start_time');
    Session::forget('session_lifetime');
    
    return redirect('/');
})->name('logout');