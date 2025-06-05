<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('users', [UserController::class, 'store'])->name('users.store');
});
