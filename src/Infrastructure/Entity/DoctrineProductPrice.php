<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity;

use App\Domain\Entity\ProductPrice;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_prices')]
#[ORM\Index(columns: ['product_id'], name: 'idx_product_id')]
class DoctrineProductPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $productId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $vendorName;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $fetchedAt;

    public function __construct(
        string $productId,
        string $vendorName,
        float $price,
        \DateTimeImmutable $fetchedAt,
    ) {
        $this->productId = $productId;
        $this->vendorName = $vendorName;
        $this->price = (string) $price;
        $this->fetchedAt = $fetchedAt;
    }

    public static function fromDomainEntity(ProductPrice $productPrice): self
    {
        return new self(
            $productPrice->getProductId(),
            $productPrice->getVendorName(),
            $productPrice->getPrice(),
            $productPrice->getFetchedAt()
        );
    }

    public function toDomainEntity(): ProductPrice
    {
        return new ProductPrice(
            $this->productId,
            $this->vendorName,
            (float) $this->price,
            $this->fetchedAt
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    public function getPrice(): float
    {
        return (float) $this->price;
    }

    public function getFetchedAt(): \DateTimeImmutable
    {
        return $this->fetchedAt;
    }
}
