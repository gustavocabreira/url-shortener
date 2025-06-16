<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Response;

it('should be able to generate a new access token', function (): void {
    $user = User::factory()->create();

    $payload = [
        'email' => $user->email,
        'password' => 'password',
    ];

    $response = $this->postJson(route('api.current-session.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_CREATED)
        ->and($response->json('token'))->toBeString();
});

it('should not be able to generate a new access token with invalid payload', function (): void {
    $payload = [
        'email' => '',
        'password' => '',
    ];

    $response = $this->postJson(route('api.current-session.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->json('errors.email'))->toBe(['The email field is required.'])
        ->and($response->json('errors.password'))->toBe(['The password field is required.']);
});

it('should not be able to generate a new access token with invalid credentials', function (): void {
    User::factory()->create();

    $payload = [
        'email' => 'john@example.com',
        'password' => 'wrong password',
    ];

    $response = $this->postJson(route('api.current-session.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_UNAUTHORIZED)
        ->and($response->json('message'))->toBe('The provided credentials are incorrect.');
});
