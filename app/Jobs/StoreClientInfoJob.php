<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\UserAgentParser;
use App\Models\ShortUrl;
use ClickHouseDB\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

final class StoreClientInfoJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ShortUrl $shortUrl,
        public string $ip,
        private ?UserAgentParser $userAgentParser = null,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $browser = $this->userAgentParser->browser();
        $version = $this->userAgentParser->version();
        $platform = $this->userAgentParser->platform();
        $device = $this->userAgentParser->device();

        $now = now();

        $ip = Http::get('https://api64.ipify.org?format=json')->json()['ip'];
        $response = Http::get('http://ip-api.com/json/'.$ip);
        $response = $response->json();

        $payload = [
            'event_date' => $now->toDateString(),
            'event_time' => $now->toDateTimeString(),
            'site_hash' => $this->shortUrl->hash,
            'user_ip' => $this->ip,
            'user_agent' => $this->userAgentParser->getUserAgent(),
            'browser' => $browser,
            'version' => $version,
            'platform' => $platform,
            'device' => $device,
            'continent' => data_get($response, 'continent'),
            'continentCode' => data_get($response, 'continentCode'),
            'country' => data_get($response, 'country'),
            'countryCode' => data_get($response, 'countryCode'),
            'region' => data_get($response, 'region'),
            'regionName' => data_get($response, 'regionName'),
            'city' => data_get($response, 'city'),
            'lat' => data_get($response, 'lat'),
            'lon' => data_get($response, 'lon'),
        ];

        $payload['user_ip'] = $ip;

        app(Client::class)->insert(
            table: 'clicks',
            values: [$payload],
            columns: [
                'event_date',
                'event_time',
                'site_hash',
                'user_ip',
                'user_agent',
                'browser',
                'version',
                'platform',
                'device',
                'continent',
                'continentCode',
                'country',
                'countryCode',
                'region',
                'regionName',
                'city',
                'lat',
                'lon',
            ],
        );
    }
}
