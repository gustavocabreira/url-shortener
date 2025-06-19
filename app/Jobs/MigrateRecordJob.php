<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ShortUrl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class MigrateRecordJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ShortUrl $shortUrl,
        public string $newShard
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->shortUrl->replicate()->setConnection($this->newShard)->save();
        $this->shortUrl->delete();
    }
}
