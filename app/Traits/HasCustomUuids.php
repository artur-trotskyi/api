<?php

namespace App\Traits;

use App\Constants\AppConstants;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HasCustomUuids
{
    use HasUuids;

    /**
     * Retrieve the model for a bound value.
     *
     * @param Model|Relation< $query *, *, *>  $query
     * @param mixed $value
     * @param string|null $field
     * @return Builder
     *
     * @throws ModelNotFoundException
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        if ($field && in_array($field, $this->uniqueIds()) && !Str::isUuid($value)) {
            throw new ModelNotFoundException(AppConstants::EXCEPTION_MESSAGES['invalid_uuid_with_field']);
        }
        if (!$field && in_array($this->getRouteKeyName(), $this->uniqueIds()) && !Str::isUuid($value)) {
            throw new ModelNotFoundException(AppConstants::EXCEPTION_MESSAGES['invalid_uuid_for_route_key']);
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }
}
