<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class DeprecatedMethodTest extends TestCase
{
    use WithFaker;

    /**
     * Method to test for deprecated methods.
     *
     * @param callable $callback
     * @return void
     */
    protected function assertDeprecation(callable $callback): void
    {
        // Enable handling of deprecated methods
        $this->withDeprecationHandling();

        // Execute the callback
        $callback();

        // Additional checks can be added here to verify deprecated method usage,
        // for example, checking log messages or if a deprecation warning was issued.
    }

    #[Test]
    public function it_should_report_deprecated_method_usage(): void
    {
        // Use the method to test deprecated method usage
        $this->assertDeprecation(function () {
            // Call a deprecated method or code
            $this->deprecatedMethod();
        });
    }

    // Example of a deprecated method
    protected function deprecatedMethod(): void
    {
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
    }
}
