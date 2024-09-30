<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

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
     */
    public function all(): Collection
    {
        return $this->model
            ->orderBy($this->sortBy, $this->sortOrder)
            ->get();
    }

    /**
     * Get a cursor for the model.
     *
     * @return LazyCollection
     */
    public function cursor(): LazyCollection
    {
        return $this->model->orderBy($this->sortBy, $this->sortOrder)->cursor();
    }

    /**
     * Create a new record in the database.
     *
     * @param array $data
     * @return model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
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
        $query = $this->model->where('id', $id);
        return (bool)$query->update($data);
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
