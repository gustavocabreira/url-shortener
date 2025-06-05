<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Response;

it('should be able to create a new user', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'P@ssw0rd',
        'password_confirmation' => 'P@ssw0rd',
    ];

    $response = $this->postJson(route('api.users.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_CREATED)
        ->and($response->json('name'))->toBe($payload['name'])
        ->and($response->json('email'))->toBe($payload['email']);

    $newUser = User::first();

    expect(User::count())->toBe(1)
        ->and($newUser->name)->toBe($payload['name'])
        ->and($newUser->email)->toBe($payload['email']);
});

it('should not be able to create a new user with an existing email', function () {
    $existingUser = User::factory()->create();

    $payload = [
        'name' => 'John Doe',
        'email' => $existingUser->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->postJson(route('api.users.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->json('errors.email'))->toBe(['The email has already been taken.']);

    expect(User::count())->toBe(1);
});

it('should not be able to create a new user with invalid data', function () {
    $payload = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    $response = $this->postJson(route('api.users.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->json('errors.name'))->toBe(['The name field is required.'])
        ->and($response->json('errors.email'))->toBe(['The email field is required.'])
        ->and($response->json('errors.password'))->toBe(['The password field is required.']);
});

it('should not be able to create a new user with invalid password', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '.',
        'password_confirmation' => '.',
    ];

    $response = $this->postJson(route('api.users.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->json('errors.password'))->toBe([
            'The password field must be at least 8 characters.',
            'The password field must contain at least one uppercase and one lowercase letter.',
            'The password field must contain at least one letter.',
            'The password field must contain at least one number.',
        ]);
});

it('should not be able to create a new user with invalid password confirmation', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'P@ssw0rd',
        'password_confirmation' => 'wrong password',
    ];

    $response = $this->postJson(route('api.users.store'), $payload);

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->json('errors.password'))->toBe(['The password field confirmation does not match.']);
});
