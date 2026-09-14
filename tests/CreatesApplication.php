<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");
        if (!preg_match('/(^|_)testing$/', $database)) {
            throw new \RuntimeException("Refusing to run tests against unsafe database [{$database}]. Use a database ending in _testing.");
        }

        return $app;
    }
}
