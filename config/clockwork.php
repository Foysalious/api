<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Enable Clockwork
    |--------------------------------------------------------------------------
    |
    | You can explicitly enable or disable Clockwork here. When enabled, special
    | headers for communication with the Clockwork Chrome extension will be
    | included in your application responses and requests data will be available
    | at /__clockwork url.
    | When set to null, Clockwork behavior is controlled by app.debug setting.
    | Default: null
    |
    */

    'enable' => env('CLOCKWORK_ENABLE'),

    /*
    |--------------------------------------------------------------------------
    | Enable data collection, when Clockwork is disabled
    |--------------------------------------------------------------------------
    |
    | This setting controls, whether data about application requests will be
    | recorded even when Clockwork is disabled (useful for later analysis).
    | Default: false
    |
    */

    'collect_data_always' => false,

    /*
    |--------------------------------------------------------------------------
    | Metadata storage
    |--------------------------------------------------------------------------
    |
    | You can configure how are the metadata collected by Clockwork stored.
    | Valid options are: files or sql.
    | Files storage stores the metadata in one-per-request files in a specified
    | directory.
    | Sql storage stores the metadata as rows in a sql database. You can specify
    | the database by name if defined in database.php or by path to Sqlite
    | database. Database table will be automatically created.
    | Sql storage requires PDO.
    |
    */

    'storage' => 'files',

    'storage_files_path' => storage_path('clockwork'),

    'storage_sql_database' => storage_path('clockwork.sqlite'),
    'storage_sql_table' => 'clockwork',

    /*
    |--------------------------------------------------------------------------
    | Filter collected data
    |--------------------------------------------------------------------------
    |
    | You can filter collected data by specifying what you don't want to collect
    | here.
    |
    */

    'filter' => array(
        'routes',    // collecting routes data on every request might use a lot of disk space
        'viewsData', // collecting views data, including all variables passed to the view on every request might use a lot of disk space
    ),

    /*
    |--------------------------------------------------------------------------
    | Disable data collection for certain URIs
    |--------------------------------------------------------------------------
    |
    | You can disable data collection for specific URIs by adding matching
    | regular expressions here.
    |
    */

    'filter_uris' => array(
        '/__clockwork/.*', // disable collecting data for clockwork-web assets
    ),

    /*
    |--------------------------------------------------------------------------
    | Additional data sources
    |--------------------------------------------------------------------------
    |
    | You can use this option to register additional data sources with Clockwork.
    | Keys specify the name under which the data source will be registered in the
    | IoC container, values are closures accepting Laravel application instance as
    | the only argument and returning an instance of the data source.
    |
    */

    'additional_data_sources' => array(
        // Note, this is for example only, laravel-doctrine contains Clockwork support out of the box, please follow the
        // documentation at http://www.laraveldoctrine.org/docs/current/orm/config-file

        // 'clockwork.doctrine' => function($app)
        // {
        // 	return new \Clockwork\DataSource\DoctrineDataSource($app['Doctrine\ORM\EntityManager']);
        // }
    ),

    /*
    |--------------------------------------------------------------------------
    | Register helpers
    |--------------------------------------------------------------------------
    |
    | This setting controls whether the "clock" helper function will be registered. You can use the "clock" function to
    | quickly log something to Clockwork or access the Clockwork instance.
    |
    */

    'register_helpers' => true,

    /*
    |--------------------------------------------------------------------------
    | Send Headers for AJAX request
    |--------------------------------------------------------------------------
    |
    | When trying to collect data the AJAX method can sometimes fail if it is
    | missing required headers. For example, an API might require a version
    | number using Accept headers to route the HTTP request to the correct
    | codebase.
    |
    */

    'headers' => array(// 'Accept' => 'application/vnd.com.whatever.v1+json',
    ),

    /*
    |--------------------------------------------------------------------------
    | Server-Timing
    |--------------------------------------------------------------------------
    |
    | Clockwork supports the W3C Server Timing specification, which allows for
    / collecting a simple performance metrics in a cross-browser way. Eg. in
    / Chrome, your app, database and timeline event timings will be shown
    / in the Dev Tools network tab.
    / This setting specifies the max number of timeline events that will be sent.
    | When set to false, Server-Timing headers will not be set.
    | Default: 10
    |
    */

    'server_timing' => 10

);
