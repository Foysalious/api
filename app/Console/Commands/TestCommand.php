<?php namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Sheba\Dal\BusinessWeekend\Model as BusinessWeekend;
use Sheba\Dal\Salary\Salary;
use Sheba\Dal\SalaryLog\SalaryLog;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    public function handle()
    {
        dump("Salary::count: " . Salary::count());

        dump("BusinessWeekend::count: " . BusinessWeekend::count());

        dump("SalaryLog::count: " . SalaryLog::count());

        dump("Business::count: " . Business::count());
    }
}
