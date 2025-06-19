<?php

declare(strict_types=1);

use App\Http\Controllers\Web\RedirectController;
use Illuminate\Support\Facades\Route;

Route::name('web.')->group(function (): void {
    Route::get('/q/{hash}', RedirectController::class)
        ->middleware('throttle:10,1')
        ->name('redirect.show');
});
