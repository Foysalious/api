<?php namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\QueueMonitor\MonitoredJob;
use Symfony\Component\Console\Output\ConsoleOutput;

class TestJob extends MonitoredJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function __construct()
    {
        parent::__construct();
        $this->queue = "test";
        $this->connection = "test";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ConsoleOutput $out)
    {
        $out->writeln("Test job.");
    }

    protected function getTitle()
    {
        return "Test Job.";
    }
}
