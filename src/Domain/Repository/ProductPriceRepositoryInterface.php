<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\ProductPrice;

interface ProductPriceRepositoryInterface
{
    public function save(ProductPrice $productPrice): void;

    public function findLowestPriceByProductId(string $productId): ?ProductPrice;

    /**
     * @return ProductPrice[]
     */
    public function findAllLowestPrices(): array;

    public function removeByProductId(string $productId): void;
}
