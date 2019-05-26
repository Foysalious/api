<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use Illuminate\Console\Command;

class RefreshGeneratedCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'sheba:refresh-customer-report {skip=0} {ids=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh customer report.';

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
        if ($ids = $this->argument('ids')) {
            foreach (explode(',', $ids) as $id) {
                Customer::find($id)->createOrUpdateReport();
            }
        } else {
            $this->generator->refresh((int)$this->argument('skip'));
        }

        $this->info('Done.');
    }
}