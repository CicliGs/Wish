<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
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
    public function findById(int $id): ?object
    {
        return $this->model->find($id);
    }

    /**
     * Find all models
     * @return array<object>
     */
    public function findAll(): array
    {
        return $this->model->all()->all();
    }

    /**
     * Create new model
     */
    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * Update existing model
     */
    public function update(object $model, array $data): object
    {
        if (!$model instanceof Model) {
            throw new \InvalidArgumentException('Model must be an instance of ' . Model::class);
        }
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Delete model
     */
    public function delete(object $model): bool
    {
        if (!$model instanceof Model) {
            throw new \InvalidArgumentException('Model must be an instance of ' . Model::class);
        }
        return $model->delete();
    }

    /**
     * Find models by criteria
     * @return array<object>
     */
    public function findBy(array $criteria): array
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get()->all();
    }

    /**
     * Find single model by criteria
     */
    public function findOneBy(array $criteria): ?object
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->first();
    }
}
