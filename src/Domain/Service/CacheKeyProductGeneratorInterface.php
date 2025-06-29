<?php

declare(strict_types=1);

namespace App\Domain\Service;

/**
 * Interface for generating cache keys for product-related operations.
 */
interface CacheKeyProductGeneratorInterface
{
    public function getCacheKeyForProduct(string $productId): string;

    public function getCacheKeyForAllPrices(): string;

    public function getCacheKeyForApiSource(string $apiName, string $productId): string;
}
