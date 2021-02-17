<?php namespace Sheba\Subscription\Partner;


use App\Models\Partner;
use Carbon\Carbon;
use Sheba\Dal\AutomaticSubscriptionUpgradationLog\Model as AutoBillingLogEntry;

class AutoBillingLog
{
    /**
     * @var Partner
     */
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function shootSuccess($log)
    {
        AutoBillingLogEntry::create(array_merge(['status' => 'success', 'log' => $log], $this->prepareData()));
    }

    public function shootLite($reason)
    {
        AutoBillingLogEntry::create(array_merge(['status' => 'migrate_to_lite', 'log' => "Package Migrated to lite $reason"], $this->prepareData()));
    }

    private function prepareData()
    {
        return [
            'partner_id' => $this->partner->id,
            'created_at' => Carbon::now()
        ];
    }
}
