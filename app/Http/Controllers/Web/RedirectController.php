<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Helpers\UserAgentParser;
use App\Http\Controllers\Controller;
use App\Jobs\StoreClientInfoJob;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RedirectController extends Controller
{
    public function __invoke(string $hash, Request $request, ConsistentHasher $consistentHasher): RedirectResponse
    {
        $shard = $consistentHasher->getShard($hash);

        $shortUrl = ShortUrl::on($shard)->where('hash', $hash)->firstOrFail();

        StoreClientInfoJob::dispatch(
            shortUrl: $shortUrl,
            ip: $request->ip(),
            userAgentParser: new UserAgentParser($request->userAgent()),
        );

        return redirect($shortUrl->original_url);
    }
}
