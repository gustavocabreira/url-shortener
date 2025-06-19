<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\MigrateRecordJob;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use Illuminate\Console\Command;

final class MigrateShardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shards:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate shortened urls to the appropriate shard';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $originalShards = ['shard_1', 'shard_2', 'shard_3'];
        $newShards = ['shard_1', 'shard_2', 'shard_3', 'shard_4'];

        $originalHasher = new ConsistentHasher($originalShards);
        $newHasher = new ConsistentHasher($newShards);

        foreach ($originalShards as $originalShard) {
            ShortUrl::on($originalShard)
                ->chunkById(100, function ($shortUrls) use ($originalShard, $originalHasher, $newHasher): void {
                    foreach ($shortUrls as $shortUrl) {
                        $original = $originalHasher->getShard($shortUrl->hash);
                        $new = $newHasher->getShard($shortUrl->hash);

                        if ($original === $originalShard && $new !== $originalShard) {
                            MigrateRecordJob::dispatch(shortUrl: $shortUrl, newShard: $new);
                        }
                    }
                });
        }
    }
}
