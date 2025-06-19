<?php

declare(strict_types=1);

use App\Services\ConsistentHasher;

it('resolves ConsistentHasher from container with config shards', function (): void {
    $consistentHasher = app(ConsistentHasher::class);

    expect($consistentHasher)->toBeInstanceOf(ConsistentHasher::class);

    $shard = $consistentHasher->getShard('some-key');

    expect($shard)->toBeIn(['shard_1', 'shard_2', 'shard_3']);
});
