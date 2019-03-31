<?php namespace Sheba\Reports\Complain;

use Illuminate\Console\Command;

class RefreshGeneratedCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'sheba:refresh-complain-report {skip=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh complain report.';

    private $generator;

    /**
     * Create a new migration install command instance.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle()
    {
        ini_set('max_execution_time', 24*60*60);
        $this->generator->refresh((int)$this->argument('skip'));
        $this->info('Done.');
    }
}