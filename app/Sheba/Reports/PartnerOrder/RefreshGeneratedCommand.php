<?php namespace Sheba\Reports\PartnerOrder;

use App\Models\PartnerOrder;
use Illuminate\Console\Command;

class RefreshGeneratedCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'sheba:refresh-partner-order-report {skip=0} {ids=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh partner order report.';

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
                PartnerOrder::find($id)->createOrUpdateReport();
            }
        } else {
            $this->generator->refresh((int)$this->argument('skip'));
        }

        $this->info('Done.');
    }
}