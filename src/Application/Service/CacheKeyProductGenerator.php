<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Service\CacheKeyProductGeneratorInterface;

/**
 * Cache key generator for product-related operations.
 */
class CacheKeyProductGenerator implements CacheKeyProductGeneratorInterface
{
    private string $keyPrefix;

    public function __construct(string $keyPrefix = 'price_cache:')
    {
        $this->keyPrefix = $keyPrefix;
    }

    public function getCacheKeyForProduct(string $productId): string
    {
        return $this->keyPrefix."product:{$productId}";
    }

    public function getCacheKeyForAllPrices(): string
    {
        return $this->keyPrefix.'all_prices';
    }

    public function getCacheKeyForApiSource(string $apiName, string $productId): string
    {
        return $this->keyPrefix."api_source:{$apiName}:{$productId}";
    }
}
