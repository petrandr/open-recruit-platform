<?php
namespace Tests;

use Illuminate\Contracts\Console\Kernel;

/**
 * Trait to create the application for testing.
 */
trait CreatesApplication
{
    /**
     * Creates the application instance.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }
}