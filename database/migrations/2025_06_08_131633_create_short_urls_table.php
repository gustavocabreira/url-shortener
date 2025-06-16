<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $shards = config('shards.connections');

        foreach ($shards as $shard) {
            Schema::connection($shard)->create('short_urls', function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->unsignedBigInteger('user_id');
                $blueprint->string('hash', 8)->unique();
                $blueprint->text('original_url');
                $blueprint->timestamps();

                $blueprint->index('user_id');
                $blueprint->index('hash');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $shards = config('shards.connections');

        foreach ($shards as $shard) {
            Schema::connection($shard)->dropIfExists('short_urls');
        }
    }
};
