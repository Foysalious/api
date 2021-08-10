<?php

use Dotenv\Dotenv;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

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

        $this->afterApplicationCreated(function () {
            $this->artisan('config:clear');
        });

        (new Dotenv($app->environmentPath(), $app->environmentFile()))->overload();
        (new LoadConfiguration())->bootstrap($app);

        $this->baseUrl = env('APP_URL');

        return $app;
    }

    protected function arrayHasKeys($keys, $array)
    {
        foreach ($keys as $key) {
            $this->arrayHasKey($key)->evaluate($array);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        $reflection_object = new ReflectionObject($this);
        foreach ($reflection_object->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
