<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository implementation providing common CRUD operations
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The model instance
     */
    protected Model $model;

    /**
     * Create a new repository instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find model by ID
     */
    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find all models
     */
    public function findAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Create new model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update existing model
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Delete model
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Find models by criteria
     */
    public function findBy(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get();
    }

    /**
     * Find single model by criteria
     */
    public function findOneBy(array $criteria): ?Model
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->first();
    }
}
