<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use App\Services\HashGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ShortUrlController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_url' => ['required', 'url'],
        ]);

        $hash = HashGenerator::generate(
            originalUrl: $validated['original_url'],
            userId: Auth::user()->id
        );

        $shards = config('shards.connections');
        $consistentHasher = new ConsistentHasher($shards);

        $shard = $consistentHasher->getShard($hash);

        if (ShortUrl::on($shard)->where('hash', $hash)->exists()) {
            return response()->json(['message' => 'Short URL already exists'], 409);
        }

        return ShortUrl::on($shard)->create([
            'user_id' => Auth::user()->id,
            'hash' => $hash,
            'original_url' => $validated['original_url'],
        ]);
    }
}
