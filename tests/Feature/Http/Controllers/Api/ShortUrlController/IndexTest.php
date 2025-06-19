<?php

declare(strict_types=1);

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    Config::set('shards.connections', [
        'shard_1',
        'shard_2',
    ]);

    ShortUrl::on('shard_1')->create([
        'user_id' => $this->user->id,
        'hash' => 'abc123',
        'original_url' => 'https://example.com',
    ]);

    ShortUrl::on('shard_2')->create([
        'user_id' => $this->user->id,
        'hash' => 'xyz789',
        'original_url' => 'https://laravel.com',
    ]);
});

it('returns paginated merged short urls across all shards', function (): void {
    $response = $this->actingAs($this->user)->getJson(
        route('api.short-urls.index', [
            'page' => 1,
        ])
    );

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['hash', 'original_url', 'created_at', 'updated_at'],
            ],
            'current_page',
            'last_page',
            'total',
        ]);

    expect($response->json('data'))->toHaveCount(2)
        ->and($response->json('current_page'))->toBe(1)
        ->and($response->json('last_page'))->toBe(1)
        ->and($response->json('total'))->toBe(2);
});

it('returns second page correctly', function (): void {
    $response = $this->actingAs($this->user)->getJson(
        route('api.short-urls.index', [
            'page' => 2,
            'per_page' => 1,
        ])
    );

    $response->assertOk();

    expect($response['data'])->toHaveCount(1);
    expect($response['current_page'])->toBe(2);
    expect($response['last_page'])->toBe(2);
});
