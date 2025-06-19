<?php

declare(strict_types=1);

use App\Jobs\MigrateRecordJob;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\ConsistentHasher;
use Illuminate\Support\Facades\Queue;

it('should dispatch a job for each record to migrate', function (): void {
    Queue::fake();

    $originalShards = ['shard_1', 'shard_2', 'shard_3'];
    $newShards = ['shard_1', 'shard_2', 'shard_3', 'shard_4'];

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

    expect($hash)->not()->toBeNull();

    ShortUrl::on($originalShard)->create([
        'user_id' => User::factory()->create()->id,
        'original_url' => 'https://example.com',
        'hash' => $hash,
    ]);

    $this->artisan('shards:migrate');

    Queue::assertPushed(MigrateRecordJob::class, function (MigrateRecordJob $migrateRecordJob) use ($hash, $newShard): bool {
        return $migrateRecordJob->shortUrl->hash === $hash
            && $migrateRecordJob->shortUrl->original_url === 'https://example.com'
            && $migrateRecordJob->newShard === $newShard;
    });
});
