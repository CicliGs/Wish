<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Base repository interface providing common CRUD operations
 */
interface BaseRepositoryInterface
{
    /**
     * Find model by ID
     */
    public function findById(int $id): ?object;

    /**
     * Find all models
     * 
     * @return array<object>
     */
    public function findAll(): array;

    /**
     * Create new model
     */
    public function create(array $data): object;

    /**
     * Update existing model
     */
    public function update(object $model, array $data): object;

    /**
     * Delete model
     */
    public function delete(object $model): bool;

    /**
     * Find models by criteria
     * 
     * @return array<object>
     */
    public function findBy(array $criteria): array;

    /**
     * Find single model by criteria
     */
    public function findOneBy(array $criteria): ?object;
}
