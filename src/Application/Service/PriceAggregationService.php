<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\ProductPrice;
use App\Domain\Repository\ProductPriceRepositoryInterface;
use App\Domain\Service\PriceFetcherInterface;
use Psr\Log\LoggerInterface;

class PriceAggregationService
{
    private ProductPriceRepositoryInterface $repository;
    private LoggerInterface $logger;

    /** @var PriceFetcherInterface[] */
    private array $priceFetchers;

    public function __construct(
        ProductPriceRepositoryInterface $repository,
        LoggerInterface $logger,
        PriceFetcherInterface ...$priceFetchers,
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->priceFetchers = $priceFetchers;
    }

    public function aggregatePricesForProduct(string $productId): void
    {
        $this->logger->info("Starting price aggregation for product: {$productId}");

        $allPrices = [];
        $now = new \DateTimeImmutable();

        foreach ($this->priceFetchers as $fetcher) {
            try {
                $prices = $fetcher->fetchPricesForProduct($productId);

                foreach ($prices as $priceData) {
                    $allPrices[] = new ProductPrice(
                        $productId,
                        $priceData->getVendor(),
                        $priceData->getPrice(),
                        $now
                    );
                }

                $this->logger->info(
                    'Fetched '.\count($prices)." prices from {$fetcher->getName()}"
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    "Failed to fetch prices from {$fetcher->getName()}: ".$e->getMessage()
                );
            }
        }

        if (empty($allPrices)) {
            $this->logger->warning("No prices found for product: {$productId}");

            return;
        }

        $lowestPrice = $this->findLowestPrice($allPrices);

        // Remove existing prices for this product before saving the new lowest
        $this->repository->removeByProductId($productId);
        $this->repository->save($lowestPrice);

        $this->logger->info(
            "Saved lowest price for product {$productId}: {$lowestPrice->getPrice()} from {$lowestPrice->getVendorName()}"
        );
    }

    /**
     * @param ProductPrice[] $prices
     */
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

    /**
     * @param string[] $productIds
     */
    public function aggregatePricesForProducts(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->aggregatePricesForProduct($productId);
        }
    }
}
