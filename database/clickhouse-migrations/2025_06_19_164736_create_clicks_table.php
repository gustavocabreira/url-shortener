<?php

declare(strict_types=1);

use Cog\Laravel\Clickhouse\Migration\AbstractClickhouseMigration;

return new class extends AbstractClickhouseMigration
{
    public function up(): void
    {
        $this->clickhouseClient->write(<<<'SQL'
            CREATE TABLE IF NOT EXISTS clicks (
                event_date Date DEFAULT toDate(event_time),
                event_time DateTime,
                site_hash String,
                user_ip String,
                user_agent String,
                browser String,
                version String,
                platform String,
                device String,
                continent String,
                continentCode String,
                country String,
                countryCode String,
                region String,
                regionName String,
                city String,
                lat String,
                lon String,
            )
            ENGINE = MergeTree
            PARTITION BY toYYYYMM(event_date)
            ORDER BY (site_hash, event_time)
            SETTINGS index_granularity = 8192
        SQL);
    }
};
