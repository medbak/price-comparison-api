<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Domain\Service\PriceFetcherInterface;
use App\Domain\ValueObject\PriceData;

/**
 * Legacy API Two Fetcher - kept for backward compatibility
 * @deprecated Use DynamicPriceFetcher instead
 */
class ApiTwoFetcher implements PriceFetcherInterface
{
    // Mock data with different structure - in real implementation this would call external API
    private array $mockData = [
        '123' => [
            ['name' => 'VendorOne', 'amount' => 20.49],
            ['name' => 'VendorTwo', 'amount' => 18.99],
            ['name' => 'VendorThree', 'amount' => 16.75],
        ],
        '456' => [
            ['name' => 'VendorOne', 'amount' => 33.99],
            ['name' => 'VendorFour', 'amount' => 31.50],
        ],
        '789' => [
            ['name' => 'VendorTwo', 'amount' => 16.25],
            ['name' => 'VendorFive', 'amount' => 13.99],
        ],
        '101' => [
            ['name' => 'VendorSix', 'amount' => 44.00],
            ['name' => 'VendorSeven', 'amount' => 41.75],
        ],
        '102' => [
            ['name' => 'VendorEight', 'amount' => 29.50],
            ['name' => 'VendorNine', 'amount' => 27.99],
        ],
    ];

    public function fetchPricesForProduct(string $productId): array
    {
        // Simulate API call delay
        usleep(150000); // 150ms

        if (!isset($this->mockData[$productId])) {
            return [];
        }

        $prices = [];
        foreach ($this->mockData[$productId] as $competitorData) {
            $prices[] = new PriceData($competitorData['name'], $competitorData['amount']);
        }

        return $prices;
    }

    public function getName(): string
    {
        return 'API Two (Legacy)';
    }
}
