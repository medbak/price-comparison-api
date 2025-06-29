<?php

declare(strict_types=1);

namespace App\Domain\Service;

interface CacheServiceInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    public function delete(string $key): bool;

    public function clear(): bool;

    public function has(string $key): bool;
}
