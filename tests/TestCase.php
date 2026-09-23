<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // RefreshDatabase wipes whatever database is configured. A cached config
        // (php artisan config:cache) ignores .env.testing, so refuse to run
        // unless the connection clearly points at a throwaway test database.
        $database = (string) $app['config']->get('database.connections.'.$app['config']->get('database.default').'.database');

        if (! str_ends_with($database, '_test') && $database !== ':memory:') {
            throw new RuntimeException(
                "Refusing to run tests against database [{$database}]. Run `php artisan config:clear` and create .env.testing pointing at a *_test database."
            );
        }

        return $app;
    }
}
