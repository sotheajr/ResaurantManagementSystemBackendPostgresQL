<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/image', [AuthController::class, 'updateImage']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dynamic table-by-table CRUD permissions via CheckPermission middleware
    Route::middleware('permission')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::put('roles/{id}/permissions', [RoleController::class, 'updatePermissions']);
    });
});

