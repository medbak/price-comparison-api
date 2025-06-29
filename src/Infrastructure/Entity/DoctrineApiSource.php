<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity;

use App\Domain\Entity\ApiSource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'api_sources')]
class DoctrineApiSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500)]
    private string $baseUrl;

    #[ORM\Column(type: 'json')]
    private array $mockData;

    #[ORM\Column(type: 'string', length: 50)]
    private string $responseFormat;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\Column(type: 'integer')]
    private int $timeoutSeconds;

    public function __construct(
        string $name,
        string $baseUrl,
        array $mockData,
        string $responseFormat,
        bool $isActive = true,
        int $timeoutSeconds = 30,
    ) {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->mockData = $mockData;
        $this->responseFormat = $responseFormat;
        $this->isActive = $isActive;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public static function fromDomainEntity(ApiSource $apiSource): self
    {
        $entity = new self(
            $apiSource->getName(),
            $apiSource->getBaseUrl(),
            $apiSource->getMockData(),
            $apiSource->getResponseFormat(),
            $apiSource->isActive(),
            $apiSource->getTimeoutSeconds()
        );

        return $entity;
    }

    public function toDomainEntity(): ApiSource
    {
        $apiSource = new ApiSource(
            $this->name,
            $this->baseUrl,
            $this->mockData,
            $this->responseFormat,
            $this->isActive,
            $this->timeoutSeconds
        );

        $reflection = new \ReflectionClass($apiSource);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($apiSource, $this->id);

        return $apiSource;
    }

    public function getId(): ?int
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
}
