<?php namespace Sheba\Reward\Commands;

use App\Models\Bonus;
use Illuminate\Console\Command;

class BonusInvalidator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:invalidate-bonus-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make bonuses invalid.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Bonus::valid()->validationDateOver()->update(['status' => 'invalid']);
        $this->info('Done');
    }
}