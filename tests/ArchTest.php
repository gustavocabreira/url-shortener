<?php

declare(strict_types=1);

arch()->preset()->php();

arch('Strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('Avoid open for extension')
    ->expect('App')
    ->classes()
    ->toBeFinal()
    ->ignoring([
        App\Http\Controllers\Controller::class,
    ]);
