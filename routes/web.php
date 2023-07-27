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