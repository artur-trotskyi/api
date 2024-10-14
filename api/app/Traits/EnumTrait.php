<?php

namespace App\Traits;

trait EnumTrait
{
    /**
     * Method to get the message value directly.
     */
    public function message(): string
    {
        return $this->value;
    }
}
