<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('permission:manage posts')->apiResource('posts', PostController::class);

    Route::get('/categories', [PostController::class, 'getCategories']);
    Route::get('/tags', [PostController::class, 'getTags']);
    Route::post('/posts', [PostController::class, 'store']);

    Route::middleware('permission:publish posts')->post('/posts/{id}/publish', [PostController::class, 'publish']);

    // Категории
    Route::apiResource('categories', CategoryController::class);

    // Теги
    Route::apiResource('tags', TagController::class);

    Route::middleware('role:Admin')->get('/admin-dashboard', function () {
        return response()->json(['message' => 'admin dashboard']);
    });

    // Получение всех пользователей
    Route::get('/users', [UserController::class, 'index']);

    // Создание нового пользователя
    Route::post('/users', [UserController::class, 'store']);

    // Получение информации о пользователе
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Обновление пользователя
    Route::put('/users/{id}', [UserController::class, 'update']);

    // Удаление пользователя
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Назначение роли пользователю
    Route::post('/users/{id}/roles', [UserController::class, 'assignRole']);

    // Назначение разрешения пользователю
    Route::post('/users/{id}/permissions', [UserController::class, 'assignPermission']);
});
