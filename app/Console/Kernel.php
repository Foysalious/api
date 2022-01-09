<?php

namespace App\Console;

use App\Console\Commands\GeneratePayslip;
use App\Console\Commands\Payslip;
use App\Console\Commands\GenerateTestAuthorList;
use App\Console\Commands\ProductUpload;
use App\Console\Commands\SetReleaseVersion;
use App\Console\Commands\TestCommand;
use App\Console\Commands\TopUpTestCommand;
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
        UploadSwaggerJson::class,
        TopUpTestCommand::class,
        Payslip::class,
        TestCommand::class,
        GeneratePayslip::class,
        GenerateTestAuthorList::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sheba:leave-adjustment')->dailyAt('00:05');
        $schedule->command('sheba:generate-payslips')->dailyAt('00:20');
    }
}
