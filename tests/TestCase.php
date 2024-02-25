<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @property \Illuminate\Foundation\Application $app Make this non-nullable for testing purposes
 *     {@see \Orchestra\Testbench\Concerns\Testing::$app}
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [];
    }
}
