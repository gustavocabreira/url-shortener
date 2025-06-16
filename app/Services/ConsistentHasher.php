<?php

declare(strict_types=1);

namespace App\Services;

final class ConsistentHasher
{
    private array $ring = [];

    public function __construct(
        array $shards,
        private int $virtualNodes = 100
    ) {
        $this->virtualNodes = $virtualNodes;

        foreach ($shards as $shard) {
            for ($i = 0; $i < $virtualNodes; $i++) {
                $hash = $this->hash("{$shard}-vn{$i}");
                $this->ring[$hash] = $shard;
            }
        }

        ksort($this->ring);
    }

    public function getShard(string $key): string
    {
        $hash = $this->hash($key);

        foreach ($this->ring as $ringHash => $shard) {
            if ($hash <= $ringHash) {
                return $shard;
            }
        }

        return reset($this->ring);
    }

    private function hash(string $value): int
    {
        return crc32($value);
    }
}
