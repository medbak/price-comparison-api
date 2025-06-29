<?php

declare(strict_types=1);

namespace App\Application\DTO\Price;

class PriceResponse
{
    public function __construct(
        public readonly string $productId,
        public readonly string $vendor,
        public readonly float $price,
        public readonly \DateTimeImmutable $fetchedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'vendor' => $this->vendor,
            'price' => $this->price,
            'fetched_at' => $this->fetchedAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
