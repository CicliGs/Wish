<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    /**
     * Find model by ID
     */
    public function findById(int $id): ?object;

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
}
