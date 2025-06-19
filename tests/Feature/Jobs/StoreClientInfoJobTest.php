<?php

declare(strict_types=1);

use App\Helpers\UserAgentParser;
use App\Jobs\StoreClientInfoJob;
use App\Models\ShortUrl;
use ClickHouseDB\Client as ClickHouseClient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Http::preventStrayRequests();
});

it('stores correct click info in ClickHouse', function (): void {
    $shortUrl = ShortUrl::factory()->make(['hash' => 'abc123']);

    Http::fake([
        'https://api64.ipify.org?format=json' => Http::response(['ip' => '1.2.3.4']),
        'http://ip-api.com/json/1.2.3.4' => Http::response([
            'continent' => 'South America',
            'continentCode' => 'SA',
            'country' => 'Brazil',
            'countryCode' => 'BR',
            'region' => 'SP',
            'regionName' => 'São Paulo',
            'city' => 'Campinas',
            'lat' => '-22.90',
            'lon' => '-47.06',
        ]),
    ]);

    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/114.0.5735.110 Safari/537.36';
    $parser = new UserAgentParser($userAgent);

    $clickhouseMock = Mockery::mock(ClickHouseClient::class);
    App::instance(ClickHouseClient::class, $clickhouseMock);

    $clickhouseMock->shouldReceive('insert')
        ->once()
        ->withArgs(function ($table, $values, $columns) use ($shortUrl): true {
            expect($table)->toBe('clicks');
            expect($values[0]['site_hash'])->toBe($shortUrl->hash);
            expect($values[0]['browser'])->toBe('Chrome');
            expect($values[0]['platform'])->toBe('Windows');
            expect($values[0]['device'])->toBe('Desktop');
            expect($values[0]['country'])->toBe('Brazil');

            return true;
        });

    $job = new StoreClientInfoJob($shortUrl, '127.0.0.1', $parser);
    $job->handle();
});

it('inserts click even if geo API fails', function (): void {
    $shortUrl = ShortUrl::factory()->make(['hash' => 'fail123']);

    Http::fake([
        'https://api64.ipify.org?format=json' => Http::response(['ip' => '8.8.8.8']),
        'http://ip-api.com/json/8.8.8.8' => Http::response([], 500),
    ]);

    $parser = new UserAgentParser('Mozilla/5.0 (X11; Linux x86_64; rv:114.0) Gecko/20100101 Firefox/114.0');

    $clickhouseMock = Mockery::mock(ClickHouseClient::class);
    App::instance(ClickHouseClient::class, $clickhouseMock);

    $clickhouseMock->shouldReceive('insert')
        ->once()
        ->withArgs(function ($table, $values, $columns) use ($shortUrl): true {
            expect($table)->toBe('clicks');
            expect($values[0]['site_hash'])->toBe($shortUrl->hash);
            expect($values[0]['browser'])->toBe('Firefox');
            expect($values[0]['country'] ?? null)->toBeNull();

            return true;
        });

    $job = new StoreClientInfoJob($shortUrl, '8.8.8.8', $parser);
    $job->handle();
});
