<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

//Route::get('/', function () {
//    return view('welcome');
//});
//Route::get('/top-navbar', function () {
//    return view('navbar-top');
//});
//Route::get('/combo-navbar', function () {
//    return view('navbar-combo');
//});

Route::get('/', [UserController::class, 'signIn'])->name('/');
Route::get('forgot-password', [UserController::class, 'passwordReset'])->name('auth.passwordReset');
Route::get('email-confirmation', [UserController::class, 'emailConfirmation'])->name('auth.emailConfirmation');
Route::get('reset-password', [UserController::class, 'resetPassword'])->name('auth.resetPassword');
Route::post('user-login', [UserController::class, 'login'])->name('auth.login');

Route::middleware(['web', 'auth'])->group(function() {
    Route::get('sign-out', [UserController::class, 'logOut'])->name('user.logout');
    Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');
});
