<?php

declare(strict_types=1);

namespace App\Services;

final class HashGenerator
{
    public static function generate(string $originalUrl, int $userId): string
    {
        $string = sprintf('%s%s', $originalUrl, $userId);

        return mb_substr(hash('sha256', $string), 0, 8);
    }
}
