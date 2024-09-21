<?php

namespace App\Repositories;

use App\Exceptions\ApiExceptionHandler;
use App\Repositories\Interfaces\BaseInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseInterface
{
    public string $sortBy = 'created_at';
    public string $sortOrder = 'asc';
    protected Model $model;

    /**
     * Repo Constructor
     * Override to clarify typehinted model.
     *
     * @param Model $model Repo DB ORM Model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all instances of model.
     *
     * @return Collection
     * @throws ApiExceptionHandler
     */
    public function all(): Collection
    {
        try {
            return $this->model
                ->orderBy($this->sortBy, $this->sortOrder)
                ->get();
        } catch (Exception $e) {
            throw new ApiExceptionHandler($e);
        }
    }

    /**
     * Create a new record in the database.
     *
     * @param array $data
     * @return model
     * @throws ApiExceptionHandler
     */
    public function create(array $data): Model
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            throw new ApiExceptionHandler($e);
        }
    }

    /**
     * Show the record with the given id.
     *
     * @param string $id
     * @return Model|null
     */
    public function getById(string $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Update record in the database and get data back.
     *
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        try {
            $query = $this->model->where('id', $id);
            return (bool)$query->update($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove record from the database.
     *
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        return (bool)$this->model->destroy($id);
    }
}
