<?php

namespace App\Services;

use App\Constants\AppConstants;
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
     *
     * @var object
     */
    public object $repo;

    /**
     * Get all data.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->repo->all();
    }

    /**
     * Create new record.
     *
     * @param array $data
     * @return model
     */
    public function create(array $data): Model
    {
        return $this->repo->create($data);
    }

    /**
     * Find record by id.
     *
     * @param string $id
     * @return Model|null
     */
    public function getById(string $id): ?Model
    {
        if (!Str::isUuid($id)) {
            throw new NotFoundHttpException(AppConstants::EXCEPTION_MESSAGES['invalid_uuid_with_field']);
        }

        $model = $this->repo->getById($id);
        if (!$model) {
            throw new NotFoundHttpException(AppConstants::EXCEPTION_MESSAGES['data_not_found']);
        }

        return $model;
    }

    /**
     * Update data.
     *
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        return (bool)$this->repo->update($id, $data);
    }

    /**
     * Delete record by id.
     *
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        return $this->repo->destroy($id);
    }
}
