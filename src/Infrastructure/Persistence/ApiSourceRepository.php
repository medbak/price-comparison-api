<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\ApiSource;
use App\Domain\Repository\ApiSourceRepositoryInterface;
use App\Infrastructure\Entity\DoctrineApiSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiSourceRepository extends ServiceEntityRepository implements ApiSourceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctrineApiSource::class);
    }

    public function save(ApiSource $apiSource): void
    {
        $doctrineEntity = DoctrineApiSource::fromDomainEntity($apiSource);

        $this->getEntityManager()->persist($doctrineEntity);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?ApiSource
    {
        $doctrineEntity = $this->find($id);

        return $doctrineEntity ? $doctrineEntity->toDomainEntity() : null;
    }

    public function findByName(string $name): ?ApiSource
    {
        $doctrineEntity = $this->findOneBy(['name' => $name]);

        return $doctrineEntity ? $doctrineEntity->toDomainEntity() : null;
    }

    public function findAllActive(): array
    {
        $doctrineEntities = $this->findBy(['isActive' => true]);

        return array_map(
            fn (DoctrineApiSource $entity) => $entity->toDomainEntity(),
            $doctrineEntities
        );
    }

    public function findAll(): array
    {
        $doctrineEntities = parent::findAll();

        return array_map(
            fn (DoctrineApiSource $entity) => $entity->toDomainEntity(),
            $doctrineEntities
        );
    }

    public function remove(ApiSource $apiSource): void
    {
        $doctrineEntity = $this->findOneBy(['name' => $apiSource->getName()]);

        if ($doctrineEntity) {
            $this->getEntityManager()->remove($doctrineEntity);
            $this->getEntityManager()->flush();
        }
    }
}
