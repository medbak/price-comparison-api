<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\PriceFetcherInterface;
use App\Domain\ValueObject\PriceData;

class ApiOnePriceFetcher implements PriceFetcherInterface
{
    // Mock data - in real implementation this would call external API
    private array $mockData = [
        '123' => [
            ['vendor' => 'ShopA', 'price' => 19.99],
            ['vendor' => 'ShopB', 'price' => 17.49],
            ['vendor' => 'ShopC', 'price' => 22.00],
        ],
        '456' => [
            ['vendor' => 'ShopA', 'price' => 35.99],
            ['vendor' => 'ShopB', 'price' => 32.49],
            ['vendor' => 'ShopD', 'price' => 38.00],
        ],
        '789' => [
            ['vendor' => 'ShopA', 'price' => 15.99],
            ['vendor' => 'ShopE', 'price' => 14.99],
        ],
    ];

    public function fetchPricesForProduct(string $productId): array
    {
        // Simulate API call delay
        usleep(100000); // 100ms

        if (!isset($this->mockData[$productId])) {
            return [];
        }

        $prices = [];
        foreach ($this->mockData[$productId] as $priceInfo) {
            $prices[] = new PriceData($priceInfo['vendor'], $priceInfo['price']);
        }

        return $prices;
    }

    public function getName(): string
    {
        return 'API One';
    }
}
