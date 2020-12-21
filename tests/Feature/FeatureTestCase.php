<?php namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use TestCase;

class FeatureTestCase extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        // $this->seed();
    }

    public function get($uri, array $headers = [])
    {
        $uri = trim($this->baseUrl, '/') . '/' . trim($uri, '/');
        return parent::get($uri, $headers);
    }

    /*public function runDatabaseMigrations()
    {
        $this->artisan('migrate');

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }*/
}
