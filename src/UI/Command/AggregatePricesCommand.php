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
    description: 'Aggregate prices from all configured sources'
)]
class AggregatePricesCommand extends Command
{
    private PriceAggregationService $aggregationService;

    public function __construct(PriceAggregationService $aggregationService)
    {
        parent::__construct();
        $this->aggregationService = $aggregationService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('product-ids', InputArgument::OPTIONAL, 'Comma-separated product IDs to aggregate (defaults to all)', '123,456,789')
            ->addOption('single', 's', InputOption::VALUE_REQUIRED, 'Aggregate prices for a single product ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $singleProductId = $input->getOption('single');

        if ($singleProductId) {
            $io->info("Aggregating prices for product: {$singleProductId}");
            $this->aggregationService->aggregatePricesForProduct($singleProductId);
            $io->success("Price aggregation completed for product {$singleProductId}");
            return Command::SUCCESS;
        }

        $productIdsStr = $input->getArgument('product-ids');
        $productIds = array_map('trim', explode(',', $productIdsStr));

        $io->info('Starting price aggregation for products: ' . implode(', ', $productIds));

        foreach ($productIds as $productId) {
            $io->note("Processing product: {$productId}");
            $this->aggregationService->aggregatePricesForProduct($productId);
        }

        $io->success('Price aggregation completed successfully!');

        return Command::SUCCESS;
    }
}
