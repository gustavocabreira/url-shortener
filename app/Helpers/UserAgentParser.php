<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str;

final class UserAgentParser
{
    public function __construct(
        private readonly string $userAgent
    ) {}

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function browser(): string
    {
        $ua = $this->userAgent;

        return match (true) {
            Str::contains($ua, 'Firefox') => 'Firefox',
            Str::contains($ua, ['OPR', 'Opera']) => 'Opera',
            Str::contains($ua, 'Edg') => 'Edge',
            Str::contains($ua, 'Chrome') && ! Str::contains($ua, 'Chromium') => 'Chrome',
            Str::contains($ua, 'Safari') && ! Str::contains($ua, 'Chrome') => 'Safari',
            Str::contains($ua, ['MSIE', 'Trident']) => 'Internet Explorer',
            default => 'Unknown',
        };
    }

    public function version(): string
    {
        $browser = $this->browser();
        $ua = $this->userAgent;

        $patterns = [
            'Firefox' => '/Firefox\/([\d\.]+)/',
            'Chrome' => '/Chrome\/([\d\.]+)/',
            'Safari' => '/Version\/([\d\.]+)/',
            'Opera' => '/(Opera|OPR)\/([\d\.]+)/',
            'Edge' => '/Edg\/([\d\.]+)/',
            'Internet Explorer' => '/(MSIE\s|rv:)([\d\.]+)/',
        ];

        if (! isset($patterns[$browser])) {
            return 'Unknown';
        }

        preg_match($patterns[$browser], $ua, $matches);

        return $matches[2] ?? $matches[1] ?? 'Unknown';
    }

    public function platform(): string
    {
        $ua = $this->userAgent;

        return match (true) {
            Str::contains($ua, 'Windows') => 'Windows',
            Str::contains($ua, 'Macintosh') => 'Mac',
            Str::contains($ua, 'Linux') => 'Linux',
            Str::contains($ua, 'Android') => 'Android',
            Str::contains($ua, ['iPhone', 'iPad']) => 'iOS',
            default => 'Unknown',
        };
    }

    public function device(): string
    {
        $ua = $this->userAgent;

        return match (true) {
            Str::contains($ua, 'Mobile') => 'Mobile',
            Str::contains($ua, 'Tablet') => 'Tablet',
            default => 'Desktop',
        };
    }
}
