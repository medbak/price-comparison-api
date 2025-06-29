<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Domain\Service\CacheServiceInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;

class PriceCache implements CacheServiceInterface
{
    private Client $redis;
    private LoggerInterface $logger;
    private string $keyPrefix;
    private int $defaultTtl;

    public function __construct(
        Client $redis,
        LoggerInterface $logger,
        string $keyPrefix = 'price_cache:',
        int $defaultTtl = 3600,
    ) {
        $this->redis = $redis;
        $this->logger = $logger;
        $this->keyPrefix = $keyPrefix;
        $this->defaultTtl = $defaultTtl;
    }

    public function get(string $key): mixed
    {
        try {
            $fullKey = $this->keyPrefix.$key;
            $value = $this->redis->get($fullKey);

            if (null === $value) {
                return null;
            }

            $decoded = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
            $this->logger->debug("Cache hit for key: {$key}");

            return $decoded;
        } catch (\Exception $e) {
            $this->logger->error("Cache get error for key {$key}: ".$e->getMessage());

            return null;
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        try {
            $fullKey = $this->keyPrefix.$key;
            $ttl = $ttl ?? $this->defaultTtl;

            $encoded = json_encode($value, \JSON_THROW_ON_ERROR);
            $result = $this->redis->setex($fullKey, $ttl, $encoded);

            $this->logger->debug("Cache set for key: {$key}, TTL: {$ttl}");

            return 'OK' === $result;
        } catch (\Exception $e) {
            $this->logger->error("Cache set error for key {$key}: ".$e->getMessage());

            return false;
        }
    }

    public function delete(string $key): bool
    {
        try {
            $fullKey = $this->keyPrefix.$key;
            $result = $this->redis->del($fullKey);

            $this->logger->debug("Cache delete for key: {$key}");

            return $result > 0;
        } catch (\Exception $e) {
            $this->logger->error("Cache delete error for key {$key}: ".$e->getMessage());

            return false;
        }
    }

    public function clear(): bool
    {
        try {
            $pattern = $this->keyPrefix.'*';
            $keys = $this->redis->keys($pattern);

            if (!empty($keys)) {
                $this->redis->del($keys);
            }

            $this->logger->info("Cache cleared for pattern: {$pattern}");

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Cache clear error: '.$e->getMessage());

            return false;
        }
    }

    public function has(string $key): bool
    {
        try {
            $fullKey = $this->keyPrefix.$key;

            return $this->redis->exists($fullKey) > 0;
        } catch (\Exception $e) {
            $this->logger->error("Cache exists check error for key {$key}: ".$e->getMessage());

            return false;
        }
    }

    public function getCacheKeyForProduct(string $productId): string
    {
        return "product_price:{$productId}";
    }

    public function getCacheKeyForAllPrices(): string
    {
        return 'all_prices';
    }

    public function getCacheKeyForApiSource(string $apiName, string $productId): string
    {
        return "api_source:{$apiName}:{$productId}";
    }
}
