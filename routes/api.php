<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Task\Controllers\TaskController;
use App\Modules\Authentication\Controllers\AuthenticationController;

Route::prefix('auth')->group(function () {

    Route::post('login', [AuthenticationController::class, 'store'])->name('login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::delete('logout', [AuthenticationController::class, 'destroy']);
        Route::get('profile', [AuthenticationController::class, 'profile']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::ApiResource('task', TaskController::class);   
});

