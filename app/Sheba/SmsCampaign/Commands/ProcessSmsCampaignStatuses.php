<?php namespace Sheba\SmsCampaign\Commands;

use Illuminate\Console\Command;
use Sheba\SmsCampaign\SmsLogs;

class ProcessSmsCampaignStatuses extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:process-sms-campaign-statuses';

    /** @var string The console command description. */
    protected $description = 'Campaign sms status change';

    /**
     * Execute the console command.
     * @param SmsLogs $logs
     */
    public function handle(SmsLogs $logs)
    {
        $logs->processLogs();
    }
}
