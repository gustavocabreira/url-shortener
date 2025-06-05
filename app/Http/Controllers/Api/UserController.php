<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class UserController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());

        return response()->json(new UserResource($user), Response::HTTP_CREATED);
    }
}
