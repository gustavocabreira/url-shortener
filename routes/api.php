<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CurrentSessionController;
use App\Http\Controllers\Api\ShortUrlController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function (): void {
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::post('login', [CurrentSessionController::class, 'store'])->name('current-session.store');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('short-urls', ShortUrlController::class);
    });
});
