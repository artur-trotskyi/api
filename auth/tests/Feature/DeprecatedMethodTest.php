<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\WithoutDeprecationHandlingTrait;

class DeprecatedMethodTest extends TestCase
{
    use WithFaker;
    //use WithoutDeprecationHandlingTrait;

    public function testShouldReportDeprecatedMethodUsage(): void
    {
        $this->deprecatedMethod();
    }

    // Example of a deprecated method
    protected function deprecatedMethod(): void
    {
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
    }
}
