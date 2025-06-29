<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

/**
 * Mock Data Provider for testing and development
 * Provides standardized test data for different API formats.
 */
class MockDataProvider
{
    /**
     * Get mock data for API One format (vendor/price structure).
     */
    public static function getApiOneData(): array
    {
        return [
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
            '101' => [
                ['vendor' => 'ShopA', 'price' => 45.99],
                ['vendor' => 'ShopF', 'price' => 42.50],
            ],
            '102' => [
                ['vendor' => 'ShopB', 'price' => 28.75],
                ['vendor' => 'ShopG', 'price' => 30.00],
            ],
        ];
    }

    /**
     * Get mock data for API Two format (name/amount structure).
     */
    public static function getApiTwoData(): array
    {
        return [
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
    }

    /**
     * Get mock data for API Three format (supplier/cost structure).
     */
    public static function getApiThreeData(): array
    {
        return [
            '123' => [
                ['supplier' => 'SupplierAlpha', 'cost' => 18.25],
                ['supplier' => 'SupplierBeta', 'cost' => 19.75],
            ],
            '456' => [
                ['supplier' => 'SupplierGamma', 'cost' => 34.50],
                ['supplier' => 'SupplierDelta', 'cost' => 36.25],
            ],
            '789' => [
                ['supplier' => 'SupplierEpsilon', 'cost' => 15.50],
            ],
            '101' => [
                ['supplier' => 'SupplierZeta', 'cost' => 43.25],
                ['supplier' => 'SupplierEta', 'cost' => 40.50],
            ],
            '102' => [
                ['supplier' => 'SupplierTheta', 'cost' => 28.25],
                ['supplier' => 'SupplierIota', 'cost' => 26.75],
            ],
        ];
    }

    /**
     * Get all available product IDs across all mock data sources.
     */
    public static function getAllProductIds(): array
    {
        return ['123', '456', '789', '101', '102'];
    }

    /**
     * Get mock data by format type.
     */
    public static function getDataByFormat(string $format): array
    {
        return match ($format) {
            'api_one' => self::getApiOneData(),
            'api_two' => self::getApiTwoData(),
            'api_three' => self::getApiThreeData(),
            default => throw new \InvalidArgumentException("Unknown format: {$format}"),
        };
    }

    /**
     * Simulate API response with random delays and occasional failures.
     */
    public static function simulateApiCall(string $apiName, string $productId, array $data): array
    {
        // Simulate network delay (50-200ms)
        $delay = rand(50000, 200000);
        usleep($delay);

        // Simulate occasional API failures (5% chance)
        if (rand(1, 100) <= 5) {
            throw new \RuntimeException("Simulated API failure for {$apiName}");
        }

        // Return the mock data for the product
        return $data[$productId] ?? [];
    }
}
