<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\HashGenerator;

it('generates a hash', function () {
    $user = User::factory()->create();
    $hash = HashGenerator::generate('https://example.com', $user->id);
    expect($hash)->toBe('614fb008');
});

it('should not generate the same hash for different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $hash1 = HashGenerator::generate('https://example.com', $user1->id);
    $hash2 = HashGenerator::generate('https://example.com', $user2->id);

    expect($hash1)->not->toBe($hash2);
});
