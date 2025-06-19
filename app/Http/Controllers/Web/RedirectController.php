<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use Illuminate\Http\RedirectResponse;

final class RedirectController extends Controller
{
    public function __invoke(string $hash): RedirectResponse
    {
        $shards = config('shards.connections');
        $consistentHasher = new ConsistentHasher($shards);
        $shard = $consistentHasher->getShard($hash);

        $url = ShortUrl::on($shard)->where('hash', $hash)->firstOrFail();

        return redirect($url->original_url);
    }
}
