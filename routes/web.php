<?php

use App\Http\Controllers\FilesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\InstitutionsController;
use App\Http\Controllers\BansController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\PracticesController;
use App\Http\Controllers\ChatsController;

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
Route::get('/', [ MainController::class, 'index' ])
    ->name('index');

// Авторизация
Route::get('/authorization', [ AuthorizationController::class, 'index' ])
    ->name('authorization')
    ->middleware('required_to_be_guest');

// Авторизация - отправка формы
Route::post('/authorization', [ AuthorizationController::class, 'index' ])
    ->middleware('required_to_be_guest');

// Регистрация
Route::get('/registration', [ RegistrationController::class, 'index' ])
    ->name('registration')
    ->middleware('required_to_be_guest');
// Регистрация - отправка формы
Route::post('/registration', [ RegistrationController::class, 'index' ])
    ->middleware('required_to_be_guest');

// Выход из аккаунта
Route::get('/logout', [ MainController::class, 'logout' ])
    ->name('logout')
    ->middleware('required_to_be_user');

// Список пользователей
Route::get('/users', [ UsersController::class, 'all' ])
    ->name('users')
    ->middleware('required_to_be_user');

// Профиль пользователя (если ID не указан - показывается профиль текущего пользователя)
Route::get('/users/{id}/profile', [ UsersController::class, 'profile' ])
    ->name('profile')
    ->middleware('required_to_be_user')
    ->where('id', '[0-9]+');
Route::get('/profile', [ UsersController::class, 'profile' ])
    ->name('my_profile')
    ->middleware('required_to_be_user')
    ->where('id', '[0-9]+');

// Панель управления - регистрация новых пользователей
Route::get('/register', [ MainController::class, 'register' ])
    ->name('register')
    ->middleware('required_to_be_user', 'required_to_be_director');

// Настройки
Route::get('/settings', [ SettingsController::class, 'index' ])
    ->name('settings')
    ->middleware('required_to_be_user');
Route::post('/settings', [ SettingsController::class, 'index' ])
    ->name('settings')
    ->middleware('required_to_be_user');

// Панель администраторов
Route::group([
    'prefix' => '/administration',
    'as' => 'administration',
    'middleware' => ['required_to_be_user', 'required_to_be_administrator']
], function () {
    Route::get('/', [ AdministrationController::class, 'index' ]);

    // Роли
    Route::group([
        'prefix' => '/roles',
        'as' => '_roles'
    ], function() {
        Route::get('/', [ RolesController::class, 'index' ]);

        // Изменение роли пользователя
        Route::get('/edit/{user_id}', [ RolesController::class, 'edit'])
            ->name('_edit')
            ->where('user_id', '[0-9]+');
        Route::post('/edit/{user_id}', [ RolesController::class, 'edit'])
            ->name('_edit')
            ->where('user_id', '[0-9]+');
    });

    // Баны
    Route::group([
        'prefix' => '/bans',
        'as' => '_bans'
    ], function() {
        // Просмотр всех банов в системе
        Route::get('/', [ BansController::class, 'index' ]);

        // Выдача бана
        Route::get('/create/{user_id}', [ BansController::class, 'create'])
            ->name('_create')
            ->where('user_id', '[0-9]+');
        Route::post('/create/{user_id}', [ BansController::class, 'create'])
            ->name('_create')
            ->where('user_id', '[0-9]+');

        // Снятие бана
        Route::get('/unban/{id}', [ BansController::class, 'unban'])
            ->name('_unban')
            ->where('id', '[0-9]+');
        Route::post('/unban/{id}', [ BansController::class, 'unban'])
            ->name('_unban')
            ->where('id', '[0-9]+');

        // Удаление бана
        Route::get('/delete/{id}', [ BansController::class, 'delete'])
            ->name('_delete')
            ->where('id', '[0-9]+');
        Route::post('/delete/{id}', [ BansController::class, 'delete'])
            ->name('_delete')
            ->where('id', '[0-9]+');
    });

    // Предприятия / Учебные заведения
    Route::group([
        'prefix' => '/institutions',
        'as' => '_institutions'
    ], function() {
        Route::get('/', [ InstitutionsController::class, 'index' ]);

        // Создание предприятия / учебного заведения
        Route::get('/create', [ InstitutionsController::class, 'create'])
            ->name('_create');
        Route::post('/create', [ InstitutionsController::class, 'create'])
            ->name('_create');

        // Изменение предприятия / учебного заведения
        Route::get('/edit/{id}', [ InstitutionsController::class, 'edit'])
            ->name('_edit')
            ->where('id', '[0-9]+');
        Route::post('/edit/{id}', [ InstitutionsController::class, 'edit'])
            ->name('_edit')
            ->where('id', '[0-9]+');

        // Удаление предприятия / учебного заведения
        Route::get('/delete/{id}', [ InstitutionsController::class, 'delete'])
            ->name('_delete')
            ->where('id', '[0-9]+');
        Route::post('/delete/{id}', [ InstitutionsController::class, 'delete'])
            ->name('_delete')
            ->where('id', '[0-9]+');
    });
});

// Просмотр банов пользователя
Route::get('/users/{user_id}/bans', [ BansController::class, 'view' ])
    ->name('bans_view')
    ->where('user_id', '[0-9]+')
    ->middleware('required_to_be_user');

// Контакты
Route::group([
    'prefix' => '/contacts',
    'as' => 'contacts',
    'middleware' => ['required_to_be_user']
], function() {
    // Список контактов
    Route::get('/', [ ContactsController::class, 'index' ]);

    // Отправка запроса в контакты
    // (Создаёт и перенаправляет)
    Route::get('/create/{user_id}/{came_from_url}', [ ContactsController::class, 'create' ])
        ->name('_create')
        ->where('user_id', '[0-9]+')
        ->where('came_from_url', 'contacts|contacts_requests_incoming|contacts_requests_outcoming|profile|practices_view');

    // Удаление запроса в контакты, либо отмена is_accepted
    // (Удаляет и перенаправляет)
    Route::get('/delete/{user_id}/{came_from_url}', [ ContactsController::class, 'delete' ])
        ->name('_delete')
        ->where('user_id', '[0-9]+')
        ->where('came_from_url', 'contacts|contacts_requests_incoming|contacts_requests_outcoming|profile|practices_view');

    // Входящие заявки
    Route::get('/requests/incoming', [ ContactsController::class, 'incoming'])
        ->name('_requests_incoming');
    Route::post('/requests/incoming', [ ContactsController::class, 'incoming'])
        ->name('_requests_incoming');

    // Исходящие заявки
    Route::get('/requests/outcoming', [ ContactsController::class, 'outcoming'])
        ->name('_requests_outcoming');
    Route::post('/requests/outcoming', [ ContactsController::class, 'outcoming'])
        ->name('_requests_outcoming');
});

// Чаты
Route::group([
    'prefix' => '/chats',
    'as' => 'chats',
    'middleware' => ['required_to_be_user']
], function() {
    // Список чатов, в которых состоит пользователь
    Route::get('/', [ ChatsController::class, 'index' ]);

    // Создание личного чата между пользователями (групповые чаты (чаты практик), создаются автоматически, если что)
    // (Создаёт и перенаправляет)
    Route::get('/create/{with_user_id}', [ ChatsController::class, 'create' ])
        ->name('_create')
        ->where('with_user_id', '[0-9]+');

    // Удаление личного чата между пользователями (групповые чаты (чаты практик), удаляются автоматически, если что)
    // (Удаляет и перенаправляет)
    Route::get('/delete/{with_user_id}', [ ChatsController::class, 'delete' ])
        ->name('_delete')
        ->where('with_user_id', '[0-9]+');

    // Окно конкретного чата
    Route::get('/{chat_id}', [ ChatsController::class, 'view' ])
        ->name('_view')
        ->where('chat_id', '[0-9]+');
    Route::post('/{chat_id}', [ ChatsController::class, 'view' ])
        ->name('_view')
        ->where('chat_id', '[0-9]+');
});

// Скачивание файлов
Route::get('/files/{filename}/download', [ FilesController::class, 'download' ])
    ->name('download_file')
    ->middleware('required_to_be_user');


// Панель практик - создание, просмотр и т.п.
Route::group([
    'prefix' => '/practices',
    'as' => 'practices',
    'middleware' => ['required_to_be_user']
], function() {
    // Список практик, в которых состоит пользователь
    Route::get('/', [ PracticesController::class, 'index' ]);

    // Создание новой практики
    Route::get('/create', [ PracticesController::class, 'create' ])
        ->name('_create')
        ->middleware('required_to_be_director');
    Route::post('/create', [ PracticesController::class, 'create' ])
        ->name('_create')
        ->middleware('required_to_be_director');

    // Присоединение к существующей практике
    Route::get('/join/{registration_key?}', [ PracticesController::class, 'join' ])
        ->name('_join');
    Route::post('/join/{registration_key?}', [ PracticesController::class, 'join' ])
        ->name('_join');

    // Окно конкретной практики
    Route::get('/{practice_id}', [ PracticesController::class, 'view' ])
        ->name('_view')
        ->where('practice_id', '[0-9]+');
    Route::post('/{practice_id}', [ PracticesController::class, 'view' ])
        ->name('_view')
        ->where('practice_id', '[0-9]+');

    // Изменение практики
    Route::get('/{practice_id}/edit', [ PracticesController::class, 'edit' ])
        ->name('_edit')
        ->where('practice_id', '[0-9]+');
    Route::post('/{practice_id}/edit', [ PracticesController::class, 'edit' ])
        ->name('_edit')
        ->where('practice_id', '[0-9]+');

    // Удаление практики
    Route::get('/{practice_id}/delete', [ PracticesController::class, 'delete' ])
        ->name('_delete')
        ->where('practice_id', '[0-9]+');
    Route::post('/{practice_id}/delete', [ PracticesController::class, 'delete' ])
        ->name('_delete')
        ->where('practice_id', '[0-9]+');

    // Удаление практики
    Route::get('/{practice_id}/remove_user/{user_id}', [ PracticesController::class, 'remove_user' ])
        ->name('_remove_user')
        ->where('practice_id', '[0-9]+')
        ->where('user_id', '[0-9]+');
});
