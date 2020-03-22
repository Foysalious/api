<?php

namespace App\Console\Commands;

use App\Sheba\Release\Release;
use Illuminate\Console\Command;

class SetReleaseVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set-release-number {--release= : Release version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new Release())->set($this->option('release'));
    }
}
