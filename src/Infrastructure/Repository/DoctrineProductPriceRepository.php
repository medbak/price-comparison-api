<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ProductPrice;
use App\Domain\Repository\ProductPriceRepositoryInterface;
use App\Infrastructure\Entity\DoctrineProductPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineProductPriceRepository extends ServiceEntityRepository implements ProductPriceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctrineProductPrice::class);
    }

    public function save(ProductPrice $productPrice): void
    {
        $doctrineEntity = DoctrineProductPrice::fromDomainEntity($productPrice);

        $this->getEntityManager()->persist($doctrineEntity);
        $this->getEntityManager()->flush();
    }

    public function findLowestPriceByProductId(string $productId): ?ProductPrice
    {
        $doctrineEntity = $this->findOneBy(['productId' => $productId]);

        return $doctrineEntity ? $doctrineEntity->toDomainEntity() : null;
    }

    public function findAllLowestPrices(): array
    {
        $doctrineEntities = $this->findAll();

        return array_map(
            fn(DoctrineProductPrice $entity) => $entity->toDomainEntity(),
            $doctrineEntities
        );
    }

    public function removeByProductId(string $productId): void
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->delete()
            ->where('pp.productId = :productId')
            ->setParameter('productId', $productId);

        $qb->getQuery()->execute();
    }
}
