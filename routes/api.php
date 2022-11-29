<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TagsController;
use App\Http\Controllers\Api\PostsController;
use App\Http\Controllers\Api\HomeController;

use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::post('/change-code', [AuthController::class, 'changeCode']);

Route::group(['middleware' => ['auth:sanctum', 'check_activation']], function () {
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::get('/stats', [HomeController::class, 'index']);

    Route::resource('tags', TagsController::class)->except('edit','create');

    Route::resource('posts', PostsController::class)->except('edit','create');
    Route::post('posts/{id}/restore', [PostsController::class, 'restore']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

