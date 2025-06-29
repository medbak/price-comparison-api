<?php

declare(strict_types=1);

namespace App\Application\Service;

use Psr\Log\LoggerInterface;

class RetryService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly int $maxRetries = 3,
        private readonly int $baseDelayMs = 1000,
        private readonly float $backoffMultiplier = 2.0,
    ) {
    }

    /**
     * Execute a callable with retry logic and exponential backoff.
     *
     * @template T
     *
     * @param callable(): T              $callable
     * @param class-string<\Throwable>[] $retryableExceptions
     *
     * @return T
     *
     * @throws \Throwable
     */
    public function execute(callable $callable, array $retryableExceptions = [\Exception::class]): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->maxRetries) {
            try {
                $result = $callable();

                if ($attempt > 0) {
                    $this->logger->info('Operation succeeded on attempt '.($attempt + 1));
                }

                return $result;
            } catch (\Throwable $e) {
                $lastException = $e;

                if (!$this->isRetryableException($e, $retryableExceptions)) {
                    $this->logger->error('Non-retryable exception: '.$e->getMessage());
                    throw $e;
                }

                if ($attempt === $this->maxRetries) {
                    $this->logger->error("Max retries ({$this->maxRetries}) exceeded. Last error: ".$e->getMessage());
                    break;
                }

                $delayMs = $this->calculateDelay($attempt);
                $this->logger->warning(
                    'Attempt '.($attempt + 1).' failed: '.$e->getMessage().
                    ". Retrying in {$delayMs}ms..."
                );

                usleep($delayMs * 1000); // Convert to microseconds
                ++$attempt;
            }
        }

        throw $lastException;
    }

    private function isRetryableException(\Throwable $exception, array $retryableExceptions): bool
    {
        foreach ($retryableExceptions as $retryableClass) {
            if ($exception instanceof $retryableClass) {
                return true;
            }
        }

        return false;
    }

    private function calculateDelay(int $attempt): int
    {
        return (int) ($this->baseDelayMs * $this->backoffMultiplier ** $attempt);
    }
}
