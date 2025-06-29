<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

class PriceData
{
    private string $vendor;
    private float $price;

    public function __construct(string $vendor, float $price)
    {
        if (empty($vendor)) {
            throw new \InvalidArgumentException('Vendor name cannot be empty');
        }

        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        $this->vendor = $vendor;
        $this->price = $price;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
