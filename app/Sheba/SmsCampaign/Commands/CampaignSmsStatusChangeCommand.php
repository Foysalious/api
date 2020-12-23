<?php namespace Sheba\SmsCampaign\Commands;

use Illuminate\Console\Command;
use Sheba\SmsCampaign\CampaignSmsStatusChanger;

class CampaignSmsStatusChangeCommand extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:process-sms-campaign-statuses';

    /** @var string The console command description. */
    protected $description = 'Campaign sms status change';

    /**
     * Execute the console command.
     * @param CampaignSmsStatusChanger $changer
     */
    public function handle(CampaignSmsStatusChanger $changer)
    {
        $changer->processPendingSms();
    }
}
