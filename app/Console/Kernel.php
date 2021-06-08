<?php namespace App\Console;

use App\Console\Commands\ProductUpload;
use App\Console\Commands\SetReleaseVersion;
use App\Console\Commands\TopUpTestCommand;
use App\Console\Commands\Payslip;
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
        TopUpTestCommand::class,
        Payslip::class,
    ];
    /*** @var Schedule */
    private $schedule;

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->companyPayrollCommands();
        $this->schedule->command('product-upload-csv')->dailyAt('00:00');
    }

    private function companyPayrollCommands()
    {
        $this->schedule->command('sheba:generate-payslips')->dailyAt('00:20');
    }
}
