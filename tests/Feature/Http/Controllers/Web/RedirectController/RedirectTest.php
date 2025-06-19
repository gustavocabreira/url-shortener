<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\ConsistentHasher;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->shards = config('shards.connections');
    $this->payload = ['original_url' => 'https://example.com'];
    $this->hasher = new ConsistentHasher($this->shards);
    $this->shard = $this->hasher->getShard($this->payload['original_url']);
});

it('should redirect to the correct original url', function (): void {
    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.short-urls.index'), $this->payload);

    $hash = $response->json('data.hash');

    $response = $this->get(route('web.redirect.show', [
        'hash' => $hash,
    ]));

    expect($response->status())->toBe(302)
        ->and($response->headers->get('location'))->toBe($this->payload['original_url']);
});

it('should return 404 if short URL does not exist', function (): void {
    $response = $this->get(route('web.redirect.show', [
        'hash' => '614fb008',
    ]));

    expect($response->status())->toBe(404);
});
