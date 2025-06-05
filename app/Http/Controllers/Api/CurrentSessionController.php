<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrentSession\LoginRequest;
use App\Http\Resources\AccessTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class CurrentSessionController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        if (! auth()->attempt($request->validated())) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        $currentUser = auth()->user();

        $accessToken = $currentUser->createToken('accessToken');

        return response()->json(new AccessTokenResource($accessToken), Response::HTTP_CREATED);
    }
}
