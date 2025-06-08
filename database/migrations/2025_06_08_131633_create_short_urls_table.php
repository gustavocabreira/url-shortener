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
            Schema::connection($shard)->create('short_urls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('hash', 8)->unique();
                $table->text('original_url');
                $table->timestamps();

                $table->index('user_id');
                $table->index('hash');
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
