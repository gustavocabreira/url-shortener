<?php

declare(strict_types=1);

arch('Models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->toOnlyBeUsedIn([
        'App\Console',
        'App\Http',
        'App\Jobs',
        'App\Models',
        'App\Providers',
        'Database\Factories',
    ]);
