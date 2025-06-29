<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\ValueObject\PriceData;

interface PriceFetcherInterface
{
    /**
     * @return PriceData[]
     */
    public function fetchPricesForProduct(string $productId): array;

    public function getName(): string;
}
