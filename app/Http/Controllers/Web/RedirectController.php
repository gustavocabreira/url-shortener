<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use Illuminate\Http\RedirectResponse;

final class RedirectController extends Controller
{
    public function __invoke(string $hash, ConsistentHasher $consistentHasher): RedirectResponse
    {
        $shard = $consistentHasher->getShard($hash);

        $shortUrl = ShortUrl::on($shard)->where('hash', $hash)->firstOrFail();

        return redirect($shortUrl->original_url);
    }
}
