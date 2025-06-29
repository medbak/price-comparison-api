<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Price\PriceResponse;
use App\Domain\Entity\ProductPrice;
use App\Domain\Repository\ProductPriceRepositoryInterface;
use App\Domain\Service\CacheKeyProductGeneratorInterface;
use App\Domain\Service\CacheServiceInterface;
use Psr\Log\LoggerInterface;

class PriceQueryService
{
    private ProductPriceRepositoryInterface $repository;
    private CacheServiceInterface $cache;
    private CacheKeyProductGeneratorInterface $cacheKeyGenerator;
    private LoggerInterface $logger;

    public function __construct(
        ProductPriceRepositoryInterface $repository,
        CacheServiceInterface $cache,
        CacheKeyProductGeneratorInterface $cacheKeyGenerator,
        LoggerInterface $logger,
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->logger = $logger;
    }

    public function getLowestPriceForProduct(string $productId): ?PriceResponse
    {
        $cacheKey = $this->cacheKeyGenerator->getCacheKeyForProduct($productId);

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if (null !== $cached && isset($cached['lowest_price'])) {
            $this->logger->debug("Cache hit for product price: {$productId}");
            $priceData = $cached['lowest_price'];

            return new PriceResponse(
                $priceData['product_id'],
                $priceData['vendor'],
                $priceData['price'],
                new \DateTimeImmutable($priceData['fetched_at'])
            );
        }

        // Fallback to database
        $productPrice = $this->repository->findLowestPriceByProductId($productId);

        if (!$productPrice) {
            return null;
        }

        $priceResponse = new PriceResponse(
            $productPrice->getProductId(),
            $productPrice->getVendorName(),
            $productPrice->getPrice(),
            $productPrice->getFetchedAt()
        );

        // Cache for 10 minutes
        $this->cache->set($cacheKey, [
            'lowest_price' => $priceResponse->toArray(),
            'total_sources' => 1,
            'successful_sources' => 1,
            'failed_sources' => 0,
        ], 600);

        return $priceResponse;
    }

    /**
     * @return PriceResponse[]
     *
     * @throws \Exception
     */
    public function getAllLowestPrices(): array
    {
        $cacheKey = $this->cacheKeyGenerator->getCacheKeyForAllPrices();

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if (null !== $cached) {
            $this->logger->debug('Cache hit for all prices');

            return array_map(
                fn (array $data) => new PriceResponse(
                    $data['product_id'],
                    $data['vendor'],
                    $data['price'],
                    new \DateTimeImmutable($data['fetched_at'])
                ),
                $cached
            );
        }

        // Fallback to database
        $productPrices = $this->repository->findAllLowestPrices();

        $priceResponses = array_map(
            fn (ProductPrice $price) => new PriceResponse(
                $price->getProductId(),
                $price->getVendorName(),
                $price->getPrice(),
                $price->getFetchedAt()
            ),
            $productPrices
        );

        // Cache for 5 minutes
        $cacheData = array_map(fn (PriceResponse $response) => $response->toArray(), $priceResponses);
        $this->cache->set($cacheKey, $cacheData, 300);

        return $priceResponses;
    }
}
