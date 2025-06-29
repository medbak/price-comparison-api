<?php

declare(strict_types=1);

namespace App\UI\Command;

use App\Application\Service\PriceAggregationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:aggregate-prices',
    description: 'Aggregate prices from all configured sources with enhanced reporting'
)]
class AggregatePricesCommand extends Command
{
    public function __construct(
        private readonly PriceAggregationService $aggregationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('product-ids', InputArgument::OPTIONAL, 'Comma-separated product IDs to aggregate (defaults to sample products)', '123,456,789,101,102')
            ->addOption('single', 's', InputOption::VALUE_REQUIRED, 'Aggregate prices for a single product ID')
            ->addOption('clear-cache', 'c', InputOption::VALUE_NONE, 'Clear all cache before aggregation')
            ->addOption('verbose-output', 'v', InputOption::VALUE_NONE, 'Show detailed aggregation results');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('clear-cache')) {
            $io->note('Clearing all cache...');
            $this->aggregationService->clearAllCache();
            $io->success('Cache cleared successfully');
        }

        $singleProductId = $input->getOption('single');
        $verboseOutput = $input->getOption('verbose-output');

        if ($singleProductId) {
            return $this->processSingleProduct($io, $singleProductId, $verboseOutput);
        }

        $productIdsStr = $input->getArgument('product-ids');
        $productIds = array_map('trim', explode(',', $productIdsStr));

        return $this->processMultipleProducts($io, $productIds, $verboseOutput);
    }

    private function processSingleProduct(SymfonyStyle $io, string $productId, bool $verbose): int
    {
        $io->info("Aggregating prices for product: {$productId}");

        try {
            $result = $this->aggregationService->aggregatePricesForProduct($productId);

            if ($result->isSuccessful()) {
                $io->success("Price aggregation completed for product {$productId}");

                if ($verbose) {
                    $this->displayAggregationResult($io, $result);
                }
            } else {
                $io->warning("No prices found for product {$productId}");
                if ($verbose && !empty($result->errors)) {
                    $io->section('Errors encountered:');
                    foreach ($result->errors as $error) {
                        $io->text("- {$error['source']}: {$error['error']}");
                    }
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to aggregate prices for product {$productId}: ".$e->getMessage());

            return Command::FAILURE;
        }
    }

    private function processMultipleProducts(SymfonyStyle $io, array $productIds, bool $verbose): int
    {
        $io->info('Starting price aggregation for products: '.implode(', ', $productIds));
        $io->progressStart(\count($productIds));

        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($productIds as $productId) {
            try {
                $result = $this->aggregationService->aggregatePricesForProduct($productId);
                $results[] = $result;

                if ($result->isSuccessful()) {
                    ++$successful;
                    if ($verbose) {
                        $io->newLine();
                        $io->note("✓ Product {$productId}: Found lowest price {$result->lowestPrice->price} from {$result->lowestPrice->vendor}");
                    }
                } else {
                    ++$failed;
                    if ($verbose) {
                        $io->newLine();
                        $io->warning("✗ Product {$productId}: No prices found");
                    }
                }
            } catch (\Exception $e) {
                ++$failed;
                $io->newLine();
                $io->error("✗ Product {$productId}: ".$e->getMessage());
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->newLine();

        $io->section('Aggregation Summary');
        $io->text([
            'Total products processed: '.\count($productIds),
            "Successful aggregations: {$successful}",
            "Failed aggregations: {$failed}",
        ]);

        if ($verbose && !empty($results)) {
            $this->displayDetailedResults($io, $results);
        }

        $io->success('Price aggregation completed!');

        return Command::SUCCESS;
    }

    private function displayAggregationResult(SymfonyStyle $io, $result): void
    {
        $io->section("Aggregation Details for Product {$result->productId}");

        $io->definitionList(
            ['Total Sources Checked' => $result->totalSourcesChecked],
            ['Successful Sources' => $result->successfulSources],
            ['Failed Sources' => $result->failedSources]
        );

        if ($result->lowestPrice) {
            $io->table(
                ['Field', 'Value'],
                [
                    ['Vendor', $result->lowestPrice->vendor],
                    ['Price', '$'.number_format($result->lowestPrice->price, 2)],
                    ['Fetched At', $result->lowestPrice->fetchedAt->format('Y-m-d H:i:s T')],
                ]
            );
        }

        if (!empty($result->errors)) {
            $io->section('Errors:');
            foreach ($result->errors as $error) {
                $io->text("• {$error['source']}: {$error['error']}");
            }
        }
    }

    private function displayDetailedResults(SymfonyStyle $io, array $results): void
    {
        $io->section('Detailed Results');

        $tableData = [];
        foreach ($results as $result) {
            $tableData[] = [
                $result->productId,
                $result->lowestPrice ? '$'.number_format($result->lowestPrice->price, 2) : 'N/A',
                $result->lowestPrice ? $result->lowestPrice->vendor : 'N/A',
                "{$result->successfulSources}/{$result->totalSourcesChecked}",
                $result->isSuccessful() ? '✓' : '✗',
            ];
        }

        $io->table(
            ['Product ID', 'Lowest Price', 'Vendor', 'Sources (Success/Total)', 'Status'],
            $tableData
        );
    }
}
