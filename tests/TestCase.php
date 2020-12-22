<?php

use Dotenv\Dotenv;
use Illuminate\Foundation\Application;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        (new Dotenv($app->environmentPath(), $app->environmentFile()))->overload();

        $this->baseUrl = env('APP_URL');

        return $app;
    }
}
