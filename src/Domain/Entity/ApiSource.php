<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class ApiSource
{
    private int $id;
    private string $name;
    private string $baseUrl;
    private array $mockData;
    private bool $isActive;
    private int $timeoutSeconds;
    private string $responseFormat; // 'api_one' or 'api_two'

    public function __construct(
        string $name,
        string $baseUrl,
        array $mockData,
        string $responseFormat,
        bool $isActive = true,
        int $timeoutSeconds = 30
    ) {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->mockData = $mockData;
        $this->responseFormat = $responseFormat;
        $this->isActive = $isActive;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getMockData(): array
    {
        return $this->mockData;
    }

    public function getResponseFormat(): string
    {
        return $this->responseFormat;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    public function getMockDataForProduct(string $productId): array
    {
        return $this->mockData[$productId] ?? [];
    }
}
