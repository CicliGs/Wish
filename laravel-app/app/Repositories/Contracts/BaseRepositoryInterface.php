<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository interface providing common CRUD operations
 */
interface BaseRepositoryInterface
{
    /**
     * Find model by ID
     */
    public function findById(int $id): ?Model;

    /**
     * Find all models
     */
    public function findAll(): Collection;

    /**
     * Create new model
     */
    public function create(array $data): Model;

    /**
     * Update existing model
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete model
     */
    public function delete(Model $model): bool;

    /**
     * Find models by criteria
     */
    public function findBy(array $criteria): Collection;

    /**
     * Find single model by criteria
     */
    public function findOneBy(array $criteria): ?Model;
}
