<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

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
            throw new InvalidArgumentException('Model must be an instance of ' . Model::class);
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
            throw new InvalidArgumentException('Model must be an instance of ' . Model::class);
        }

        return $model->delete();
    }
}
