<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CurrentSessionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::post('login', [CurrentSessionController::class, 'store'])->name('current-session.store');
});
