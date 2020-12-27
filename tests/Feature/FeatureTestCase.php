<?php namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use TestCase;

class FeatureTestCase extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
    }

    public function get($uri, array $headers = [])
    {
        $uri = trim($this->baseUrl, '/') . '/' . trim($uri, '/');
        return parent::get($uri, $headers);
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
        $this->artisan('migrate');
        $this->beforeApplicationDestroyed(function () {
            DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
        });
    }
}
