<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\ConsistentHasher;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
    $this->user = User::factory()->create();
    $this->shards = config('shards.connections');
    $this->payload = ['original_url' => 'https://example.com'];
    $this->hasher = new ConsistentHasher($this->shards);
    $this->shard = $this->hasher->getShard($this->payload['original_url']);
});

it('allows up to 10 requests per minute to /q/{hash}', function (): void {
    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.short-urls.index'), $this->payload);

    $hash = $response->json('data.hash');
    $endpoint = sprintf('/q/%s', $hash);

    foreach (range(1, 10) as $i) {
        $this->get($endpoint)->assertRedirect();
    }
});

it('blocks requests after 10 requests per minute to /q/{hash}', function (): void {
    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.short-urls.index'), $this->payload);

    $hash = $response->json('data.hash');
    $endpoint = sprintf('/q/%s', $hash);

    foreach (range(1, 10) as $i) {
        $this->get($endpoint)->assertRedirect();
    }

    $this->get($endpoint)->assertTooManyRequests();
});

it('blocks requests after 10 requests if url is not found', function (): void {
    $endpoint = sprintf('/q/%s', 'invalid');

    foreach (range(1, 10) as $i) {
        $this->get($endpoint)->assertNotFound();
    }

    $this->get($endpoint)->assertTooManyRequests();
});
