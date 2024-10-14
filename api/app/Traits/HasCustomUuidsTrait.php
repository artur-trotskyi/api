<?php

namespace App\Traits;

use App\Enums\ExceptionMessagesEnum;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HasCustomUuidsTrait
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
            throw new ModelNotFoundException(ExceptionMessagesEnum::InvalidUuidWithField->message());
        }
        if (!$field && in_array($this->getRouteKeyName(), $this->uniqueIds()) && !Str::isUuid($value)) {
            throw new ModelNotFoundException(ExceptionMessagesEnum::InvalidUuidForRouteKey->message());
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }
}
