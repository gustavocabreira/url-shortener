<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ShortUrlController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_url' => ['required', 'url'],
        ]);

        $hash = mb_substr(hash('sha256', $validated['original_url']), 0, 8);

        $shards = config('shards.connections');
        $hasher = new ConsistentHasher($shards);

        $shard = $hasher->getShard($hash);

        if (ShortUrl::on($shard)->where('hash', $hash)->exists()) {
            return response()->json(['message' => 'Short URL already exists'], 409);
        }

        $shortUrl = ShortUrl::on($shard)->create([
            'user_id' => Auth::user()->id,
            'hash' => $hash,
            'original_url' => $validated['original_url'],
        ]);

        return $shortUrl;
    }
}
