<?php namespace App\Console;

use App\Console\Commands\ProductUpload;
use App\Console\Commands\SetReleaseVersion;
use App\Console\Commands\UploadSwaggerJson;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Sheba\Algolia\AlgoliaSync;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ProductUpload::class,
        SetReleaseVersion::class,
        AlgoliaSync::class,
        UploadSwaggerJson::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('product-upload-csv')->dailyAt('00:00');
    }
}
