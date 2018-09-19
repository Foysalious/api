<?php namespace Sheba\Reward\Commands;

use Illuminate\Console\Command;
use Sheba\Reward\CompletedCampaignHandler;

class CampaignRewardDisburse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:campaign-reward-disburse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Campaign reward disburse';

    /**
     * Execute the console command.
     *
     * @param CompletedCampaignHandler $handler
     * @throws \Sheba\Reward\Exception\RulesTypeMismatchException
     * @throws \Sheba\Reward\Exception\RulesValueMismatchException
     */
    public function handle(CompletedCampaignHandler $handler)
    {
        $handler->run();
        $this->info("All good");
    }
}
