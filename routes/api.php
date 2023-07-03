<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', fn() => response()->json(['message' => '༼ つ ◕_◕ ༽つ']));

Route::name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::any('/authorize-teacher-from-dcsr',
        [AuthController::class, 'authorizeTeacherFromDCSR'])->name('authorize-teacher-from-dcsr');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::name('auth.')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

    Route::name('classes.')->group(function () {
        Route::get('/classes', [ClassController::class, 'index'])->name('index');
        Route::get('/classes/in-class', [ClassController::class, 'inClass'])->name('in-class');
        Route::get('/classes/{id}', [ClassController::class, 'show'])->name('show');
    });
});
