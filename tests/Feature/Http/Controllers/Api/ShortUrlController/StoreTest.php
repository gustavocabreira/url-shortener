<?php

declare(strict_types=1);

use App\Models\ShortUrl;
use App\Models\User;
use App\Services\ConsistentHasher;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->payload = ['original_url' => 'https://example.com'];
    $this->shards = config('shards.connections');
    $this->hasher = new ConsistentHasher($this->shards);
    $this->shard = $this->hasher->getShard($this->payload['original_url']);
    $this->otherShards = collect($this->shards)->reject(fn ($s) => $s === $this->shard);
});

it('stores a short URL in the correct shard', function () {
    $response = $this->actingAs($this->user)->postJson(route('api.short-urls.index'), $this->payload);

    $response->assertCreated();

    expect(ShortUrl::on($this->shard)->first()->original_url)->toBe($this->payload['original_url'])
        ->and(ShortUrl::on($this->shard)->first()->user_id)->toBe($this->user->id)
        ->and(ShortUrl::on($this->shard)->count())->toBe(1);

    $this->otherShards->each(
        fn ($shard) => expect(ShortUrl::on($shard)->count())->toBe(0)
    );
});

it('returns 409 if short URL already exists', function () {
    $hash = mb_substr(hash('sha256', $this->payload['original_url'].$this->user->id), 0, 8);

    ShortUrl::on($this->shard)->create([
        'user_id' => $this->user->id,
        'hash' => $hash,
        'original_url' => $this->payload['original_url'],
    ]);

    $response = $this->actingAs($this->user)->postJson(route('api.short-urls.index'), $this->payload);

    $response->assertStatus(409)
        ->assertJson(['message' => 'Short URL already exists']);

    expect(ShortUrl::on($this->shard)->count())->toBe(1);

    $this->otherShards->each(
        fn ($shard) => expect(ShortUrl::on($shard)->count())->toBe(0)
    );
});
