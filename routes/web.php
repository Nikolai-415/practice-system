<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\RegistrationController;

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

// Главная
Route::get('/', [MainController::class, 'index'])
    ->name('index');

// Авторизация
Route::get('/authorization', [AuthorizationController::class, 'index'])
    ->name('authorization')
    ->middleware('required_not_to_be_authorized');

// Авторизация - отправка формы
Route::post('/authorization', [AuthorizationController::class, 'index'])
    ->middleware('required_not_to_be_authorized');

// Регистрация
Route::get('/registration', [RegistrationController::class, 'index'])
    ->name('registration')
    ->middleware('required_not_to_be_authorized');

// Регистрация - отправка формы
Route::post('/registration', [RegistrationController::class, 'index'])
    ->middleware('required_not_to_be_authorized');

// Выход из аккаунта
Route::get('/logout', [MainController::class, 'logout'])
    ->name('logout')
    ->middleware('required_to_be_authorized');
