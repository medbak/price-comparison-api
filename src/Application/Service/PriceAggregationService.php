<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Price\PriceAggregationResult;
use App\Application\DTO\Price\PriceResponse;
use App\Domain\Entity\ProductPrice;
use App\Domain\Repository\ApiSourceRepositoryInterface;
use App\Domain\Repository\ProductPriceRepositoryInterface;
use App\Domain\Service\CacheKeyProductGeneratorInterface;
use App\Domain\Service\CacheServiceInterface;
use App\Infrastructure\External\DynamicPriceFetcher;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class PriceAggregationService
{
    public function __construct(
        private ProductPriceRepositoryInterface $repository,
        private ApiSourceRepositoryInterface $apiSourceRepository,
        private CacheServiceInterface $cache,
        private CacheKeyProductGeneratorInterface $cacheKeyGenerator,
        private RetryService $retryService,
        private LoggerInterface $logger
    ) {}

    public function aggregatePricesForProduct(string $productId): PriceAggregationResult
    {
        $this->logger->info("Starting price aggregation for product: {$productId}");

        // Check cache first
        $cacheKey = $this->cacheKeyGenerator->getCacheKeyForProduct($productId);
        $cachedResult = $this->cache->get($cacheKey);

        if ($cachedResult !== null) {
            $this->logger->info("Using cached result for product: {$productId}");
            return new PriceAggregationResult(
                $productId,
                $cachedResult['total_sources'],
                $cachedResult['successful_sources'],
                $cachedResult['failed_sources'],
                isset($cachedResult['lowest_price']) ? new PriceResponse(
                    $cachedResult['lowest_price']['product_id'],
                    $cachedResult['lowest_price']['vendor'],
                    $cachedResult['lowest_price']['price'],
                    new DateTimeImmutable($cachedResult['lowest_price']['fetched_at'])
                ) : null,
                $cachedResult['errors'] ?? []
            );
        }

        $apiSources = $this->apiSourceRepository->findAllActive();
        $allPrices = [];
        $errors = [];
        $successfulSources = 0;
        $now = new DateTimeImmutable();

        foreach ($apiSources as $apiSource) {
            try {
                $fetcher = new DynamicPriceFetcher(
                    $apiSource,
                    $this->retryService,
                    $this->cache,
                    $this->cacheKeyGenerator,
                    $this->logger
                );

                $prices = $fetcher->fetchPricesForProduct($productId);

                if (!empty($prices)) {
                    foreach ($prices as $priceData) {
                        $allPrices[] = new ProductPrice(
                            $productId,
                            $priceData->getVendor(),
                            $priceData->getPrice(),
                            $now
                        );
                    }
                    $successfulSources++;

                    $this->logger->info(
                        "Fetched " . count($prices) . " prices from {$apiSource->getName()}"
                    );
                } else {
                    $this->logger->warning("No prices found for product {$productId} from {$apiSource->getName()}");
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'source' => $apiSource->getName(),
                    'error' => $e->getMessage()
                ];
                $this->logger->error(
                    "Failed to fetch prices from {$apiSource->getName()}: " . $e->getMessage()
                );
            }
        }

        $failedSources = count($apiSources) - $successfulSources;
        $lowestPrice = null;

        if (!empty($allPrices)) {
            $lowestPriceEntity = $this->findLowestPrice($allPrices);

            $this->repository->removeByProductId($productId);
            $this->repository->save($lowestPriceEntity);

            $lowestPrice = new PriceResponse(
                $lowestPriceEntity->getProductId(),
                $lowestPriceEntity->getVendorName(),
                $lowestPriceEntity->getPrice(),
                $lowestPriceEntity->getFetchedAt()
            );

            $this->logger->info(
                "Saved lowest price for product {$productId}: {$lowestPriceEntity->getPrice()} from {$lowestPriceEntity->getVendorName()}"
            );
        } else {
            $this->logger->warning("No prices found for product: {$productId}");
        }

        $result = new PriceAggregationResult(
            $productId,
            count($apiSources),
            $successfulSources,
            $failedSources,
            $lowestPrice,
            $errors
        );

        $this->cache->set($cacheKey, $result->toArray(), 600);

        $allPricesCacheKey = $this->cacheKeyGenerator->getCacheKeyForAllPrices();
        $this->cache->delete($allPricesCacheKey);

        return $result;
    }

    private function findLowestPrice(array $prices): ProductPrice
    {
        $lowest = $prices[0];

        foreach ($prices as $price) {
            if ($price->isLowerPriceThan($lowest)) {
                $lowest = $price;
            }
        }

        return $lowest;
    }

    public function aggregatePricesForProducts(array $productIds): array
    {
        $results = [];

        foreach ($productIds as $productId) {
            $results[] = $this->aggregatePricesForProduct($productId);
        }

        return $results;
    }

    public function clearCacheForProduct(string $productId): void
    {
        $cacheKey = $this->cacheKeyGenerator->getCacheKeyForProduct($productId);
        $allPricesCacheKey = $this->cacheKeyGenerator->getCacheKeyForAllPrices();

        $this->cache->delete($cacheKey);
        $this->cache->delete($allPricesCacheKey);

        $this->logger->info("Cleared cache for product: {$productId}");
    }

    public function clearAllCache(): void
    {
        $this->cache->clear();
        $this->logger->info("Cleared all price cache");
    }
}
