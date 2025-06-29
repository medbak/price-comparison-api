<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\ProductPrice;
use App\Domain\Repository\ProductPriceRepositoryInterface;

readonly class PriceQueryService
{
    public function __construct(private ProductPriceRepositoryInterface $repository)
    {
    }

    public function getLowestPriceForProduct(string $productId): ?ProductPrice
    {
        return $this->repository->findLowestPriceByProductId($productId);
    }

    /**
     * @return ProductPrice[]
     */
    public function getAllLowestPrices(): array
    {
        return $this->repository->findAllLowestPrices();
    }
}
