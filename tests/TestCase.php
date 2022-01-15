<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Contracts\Console\Kernel;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, HasFactory, RefreshDatabase;

    public function setUp(): void
    {
        parent::setup();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            // We can also customise where our factories live too if we want:
            $namespace = 'Database\\Factories\\';

            // Here we are getting the model name from the class namespace
            $modelName = Str::afterLast($modelName, '\\');

            // Finally, we'll build up the full class path where
            // Laravel will find our model factory
            return $namespace.$modelName.'Factory';
        });
    }

    /**
     * @author Hasan Hafiz Pasha <mach.pasha@gmail.com>
     */
    protected function refreshTestDatabase()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 1000);

        if (!RefreshDatabaseState::$migrated) {
            /**
             * NEED TO RUN ONLY ONE TIMES
             *
             * DB::unprepared(file_get_contents(database_path('seeds/sheba_testing.sql')));
             * $this->artisan('migrate');
             */
            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }
}
