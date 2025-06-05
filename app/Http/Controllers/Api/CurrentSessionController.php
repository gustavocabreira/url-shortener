<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccessTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CurrentSessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! auth()->attempt($validated)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        $currentUser = auth()->user();

        $accessToken = $currentUser->createToken('accessToken');

        return response()->json(new AccessTokenResource($accessToken), Response::HTTP_CREATED);
    }
}
