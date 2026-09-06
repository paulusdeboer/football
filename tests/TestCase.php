<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $databaseConfig = $app['config']->get('database');

        if (
            ($databaseConfig['default'] ?? null) !== 'sqlite'
            || ($databaseConfig['connections']['sqlite']['database'] ?? null) !== ':memory:'
        ) {
            throw new RuntimeException(
                'Feature tests require the isolated in-memory SQLite database; refusing to run against another database.'
            );
        }

        return $app;
    }

    protected function setUp(): void
    {
        $connection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION');
        $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE');

        if ($connection !== 'sqlite' || $database === 'laravel') {
            throw new RuntimeException(
                'Feature tests require an isolated SQLite database; refusing to run against the development database.'
            );
        }

        parent::setUp();
    }
}
