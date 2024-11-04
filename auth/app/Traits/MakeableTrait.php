<?php

namespace App\Traits;

trait MakeableTrait
{
    /**
     * Create a new instance.
     *
     * @param  mixed  ...$parameters
     */
    public static function make(...$parameters): static
    {
        return new static(...$parameters);
    }
}
