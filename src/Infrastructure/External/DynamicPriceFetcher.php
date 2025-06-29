<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Application\Service\RetryService;
use App\Domain\Entity\ApiSource;
use App\Domain\Service\CacheKeyProductGeneratorInterface;
use App\Domain\Service\CacheServiceInterface;
use App\Domain\Service\PriceFetcherInterface;
use App\Domain\ValueObject\PriceData;
use Psr\Log\LoggerInterface;

class DynamicPriceFetcher implements PriceFetcherInterface
{
    private ApiSource $apiSource;
    private RetryService $retryService;
    private CacheServiceInterface $cache;
    private CacheKeyProductGeneratorInterface $cacheKeyGenerator;
    private LoggerInterface $logger;

    public function __construct(
        ApiSource $apiSource,
        RetryService $retryService,
        CacheServiceInterface $cache,
        CacheKeyProductGeneratorInterface $cacheKeyGenerator,
        LoggerInterface $logger
    ) {
        $this->apiSource = $apiSource;
        $this->retryService = $retryService;
        $this->cache = $cache;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->logger = $logger;
    }

    public function fetchPricesForProduct(string $productId): array
    {
        if (!$this->apiSource->isActive()) {
            $this->logger->info("API source {$this->apiSource->getName()} is inactive, skipping");
            return [];
        }

        $cacheKey = $this->cacheKeyGenerator->getCacheKeyForApiSource($this->apiSource->getName(), $productId);

        // Check cache first
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== null) {
            $this->logger->debug("Cache hit for {$this->apiSource->getName()} - product {$productId}");
            return $this->convertToPriceData($cachedData, $this->apiSource->getResponseFormat());
        }

        // Fetch with retry logic
        try {
            $prices = $this->retryService->execute(
                fn() => $this->performFetch($productId),
                [\RuntimeException::class, \JsonException::class]
            );

            // Cache the results for 5 minutes
            $this->cache->set($cacheKey, $prices, 300);

            return $this->convertToPriceData($prices, $this->apiSource->getResponseFormat());
        } catch (\Throwable $e) {
            $this->logger->error(
                "Failed to fetch prices from {$this->apiSource->getName()} for product {$productId}: " . $e->getMessage()
            );
            return [];
        }
    }

    private function performFetch(string $productId): array
    {
        // Simulate API call delay
        $delay = rand(50000, 200000); // 50-200ms
        usleep($delay);

        // Simulate potential failure (5% chance)
        if (rand(1, 100) <= 5) {
            throw new \RuntimeException("Simulated API failure for {$this->apiSource->getName()}");
        }

        $mockData = $this->apiSource->getMockDataForProduct($productId);

        if (empty($mockData)) {
            $this->logger->info("No data found for product {$productId} in {$this->apiSource->getName()}");
            return [];
        }

        $this->logger->info(
            "Fetched " . count($mockData) . " prices from {$this->apiSource->getName()} for product {$productId}"
        );

        return $mockData;
    }

    private function convertToPriceData(array $rawData, string $format): array
    {
        $prices = [];

        foreach ($rawData as $item) {
            try {
                $priceData = match ($format) {
                    'api_one' => new PriceData($item['vendor'], $item['price']),
                    'api_two' => new PriceData($item['name'], $item['amount']),
                    'api_three' => new PriceData($item['supplier'], $item['cost']),
                    default => throw new \InvalidArgumentException("Unknown format: {$format}")
                };

                $prices[] = $priceData;
            } catch (\Exception $e) {
                $this->logger->warning(
                    "Failed to convert price data for {$this->apiSource->getName()}: " . $e->getMessage()
                );
            }
        }

        return $prices;
    }

    public function getName(): string
    {
        return $this->apiSource->getName();
    }
}
