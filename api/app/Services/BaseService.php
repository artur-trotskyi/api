<?php

namespace App\Services;

use App\Enums\ExceptionMessagesEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseService
{
    use DispatchesJobs;

    /**
     * Repository.
     */
    public object $repo;

    /**
     * Get all data.
     */
    public function all(): Collection
    {
        return $this->repo->all();
    }

    /**
     * Create new record.
     */
    public function create(array $data): Model
    {
        return $this->repo->create($data);
    }

    /**
     * Find record by id.
     */
    public function getById(string $id): ?Model
    {
        if (! Str::isUuid($id)) {
            throw new NotFoundHttpException(ExceptionMessagesEnum::InvalidUuidWithField->message());
        }

        $model = $this->repo->getById($id);
        if (! $model) {
            throw new NotFoundHttpException(ExceptionMessagesEnum::DataNotFound->message());
        }

        return $model;
    }

    /**
     * Update data.
     */
    public function update(string $id, array $data): bool
    {
        return (bool) $this->repo->update($id, $data);
    }

    /**
     * Delete record by id.
     */
    public function destroy(string $id): bool
    {
        return $this->repo->destroy($id);
    }
}
