<?php

declare(strict_types=1);

arch('Globals')
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump', 'sleep', 'dispatch', 'dispatch_sync'])
    ->not->toBeUsed();

arch('Http Helpers')
    ->expect(['session', 'auth', 'request'])
    ->toOnlyBeUsedIn([
        'App\Http',
        'App\Rules',
        'App\Livewire',
        'App\Jobs\IncrementViews',
        'App\Services\Autocomplete\Types',
    ]);
