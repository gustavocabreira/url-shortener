<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShortUrl\StoreRequest;
use App\Models\ShortUrl;
use App\Services\ConsistentHasher;
use App\Services\HashGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ShortUrlController extends Controller
{
    public function index(): JsonResponse
    {
        $shards = config('shards.connections');
        $urls = collect();

        foreach ($shards as $shard) {
            $shortUrls = ShortUrl::on($shard)
                ->where('user_id', request()->user()->id)
                ->get();

            $urls = $urls->merge($shortUrls);
        }

        $urls = $urls->sortByDesc('created_at')->values();

        $perPage = request()->input('per_page', 10);
        $page = request()->input('page', 1);

        $lengthAwarePaginator = new LengthAwarePaginator(
            $urls->forPage($page, $perPage),
            $urls->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json([
            'data' => array_values($lengthAwarePaginator->items()),
            'current_page' => $lengthAwarePaginator->currentPage(),
            'last_page' => $lengthAwarePaginator->lastPage(),
            'total' => $lengthAwarePaginator->total(),
        ]);
    }

    public function store(StoreRequest $storeRequest, ConsistentHasher $consistentHasher): JsonResource
    {
        $validated = $storeRequest->validated();

        $hash = HashGenerator::generate(
            originalUrl: $validated['original_url'],
            userId: Auth::user()->id
        );

        $shard = $consistentHasher->getShard($hash);

        if (ShortUrl::on($shard)->where('hash', $hash)->exists()) {
            throw new ConflictHttpException('Short URL already exists');
        }

        return ShortUrl::on($shard)->create([
            'user_id' => Auth::user()->id,
            'hash' => $hash,
            'original_url' => $validated['original_url'],
        ])->toResource();
    }
}
