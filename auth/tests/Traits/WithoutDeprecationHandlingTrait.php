<?php

namespace Tests\Traits;

use ErrorException;

trait WithoutDeprecationHandlingTrait
{
    /**
     * Sets the configuration for displaying outdated alerts.
     * This trait should only be used in classes that extend the TestCase.
     *
     * @throws ErrorException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDeprecationHandling();
    }
}
