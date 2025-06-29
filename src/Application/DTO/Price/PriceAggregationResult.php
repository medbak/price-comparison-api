<?php

declare(strict_types=1);

namespace App\Application\DTO\Price;

class PriceAggregationResult
{
    public function __construct(
        public readonly string $productId,
        public readonly int $totalSourcesChecked,
        public readonly int $successfulSources,
        public readonly int $failedSources,
        public readonly ?PriceResponse $lowestPrice,
        public readonly array $errors = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return null !== $this->lowestPrice;
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'total_sources_checked' => $this->totalSourcesChecked,
            'successful_sources' => $this->successfulSources,
            'failed_sources' => $this->failedSources,
            'lowest_price' => $this->lowestPrice?->toArray(),
            'errors' => $this->errors,
        ];
    }
}
