<?php

declare(strict_types=1);

use App\Jobs\MigrateRecordJob;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\ConsistentHasher;

beforeEach(function (): void {
    $newShards = ['shard_1', 'shard_2', 'shard_3', 'shard_4'];

    config()->set('shards.connections', $newShards);
});

it('should migrate the record to the new shard', function (): void {
    $originalShards = ['shard_1', 'shard_2', 'shard_3'];
    $newShards = ['shard_1', 'shard_2'];

    $oldHasher = new ConsistentHasher($originalShards);
    $newHasher = new ConsistentHasher($newShards);

    $hash = null;
    $originalShard = null;
    $newShard = null;

    foreach (range(1, 10000) as $i) {
        $candidate = "key_$i";
        $old = $oldHasher->getShard($candidate);
        $new = $newHasher->getShard($candidate);

        if ($old !== $new) {
            $hash = $candidate;
            $originalShard = $old;
            $newShard = $new;
            break;
        }
    }

    $shortUrl = ShortUrl::on($originalShard)->create([
        'user_id' => User::factory()->create()->id,
        'original_url' => 'https://example.com',
        'hash' => $hash,
    ]);

    $migrateRecordJob = new MigrateRecordJob(
        shortUrl: $shortUrl,
        newShard: $newShard,
    );

    $migrateRecordJob->handle();

    expect(ShortUrl::on($newShard)->first()->original_url)->toBe('https://example.com')
        ->and(ShortUrl::on($newShard)->first()->user_id)->toBe(1)
        ->and(ShortUrl::on($newShard)->count())->toBe(1)
        ->and(ShortUrl::on($originalShard)->count())->toBe(0);
});
