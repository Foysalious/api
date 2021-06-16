<?php namespace Sheba\Database;

use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the MySql connection class as a singleton
        // because we only want to have one, and only one,
        // MySql database connection at the same time.
        $this->app->singleton('db.connection.mysql', function ($app, $parameters) {
            // First, we list the passes parameters into single
            // variables. I do this because it is far easier
            // to read than using it as eg $parameters[0].
            list($connection, $database, $prefix, $config) = $parameters;

            // Next we can initialize the connection.
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }
}
