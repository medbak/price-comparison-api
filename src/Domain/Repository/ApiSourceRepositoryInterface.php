<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\ApiSource;

interface ApiSourceRepositoryInterface
{
    public function save(ApiSource $apiSource): void;

    public function findById(int $id): ?ApiSource;

    public function findByName(string $name): ?ApiSource;

    /**
     * @return ApiSource[]
     */
    public function findAllActive(): array;

    /**
     * @return ApiSource[]
     */
    public function findAll(): array;

    public function remove(ApiSource $apiSource): void;
}
