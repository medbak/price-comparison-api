<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class ProductPrice
{
    private string $productId;
    private string $vendorName;
    private float $price;
    private \DateTimeImmutable $fetchedAt;

    public function __construct(
        string $productId,
        string $vendorName,
        float $price,
        \DateTimeImmutable $fetchedAt,
    ) {
        $this->productId = $productId;
        $this->vendorName = $vendorName;
        $this->price = $price;
        $this->fetchedAt = $fetchedAt;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getFetchedAt(): \DateTimeImmutable
    {
        return $this->fetchedAt;
    }

    public function isLowerPriceThan(self $other): bool
    {
        return $this->price < $other->price;
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'vendor' => $this->vendorName,
            'price' => $this->price,
            'fetched_at' => $this->fetchedAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
