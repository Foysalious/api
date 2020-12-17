<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use Carbon\Carbon;
use Sheba\Subscription\Exceptions\InvalidPreviousSubscriptionRules;

class PeriodicBillingHandler
{
    /** @var Partner $partner */
    private $partner;
    private $today;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
        $this->today = Carbon::today();
    }

    public function run()
    {
        if ($this->hasBillingCycleEnded()) {
            $this->partner->runSubscriptionBilling();
        }
    }

    private function migrateToLite($reason = '')
    {
        $new_package = PartnerSubscriptionPackage::find(self::FREE_PACKAGE_ID);
        $new_billing_type = BillingType::MONTHLY;
        $this->partner->subscriber()->upgradeNew($new_package, $new_billing_type);
        (new AutoBillingLog($this->partner))->shootLite($reason);
    }

    private function checkPackageChangeRequest(&$new_package, &$new_billing_type)
    {
        $request = null;
        $this->partner->load(['subscriptionUpdateRequest' => function ($q) {
            $q->where('status', 'Pending');
        }]);
        if (!$this->partner->subscriptionUpdateRequest->isEmpty()) {
            $requests = $this->partner->subscriptionUpdateRequest;
            $request = $requests->last();
            $new_package = $request->newPackage;
            $new_billing_type = $request->new_billing_type;
            foreach ($requests as $req) {
                /** @var PartnerSubscriptionUpdateRequest $req */
                $req->status = 'Rejected';
                $req->update();
            }
        }
        return $request;
    }

    public function hasBillingCycleEnded()
    {
        return $this->nextBillingDate() ? $this->nextBillingDate()->isSameDay($this->today) : true;
    }

    /**
     * FINDING PARTNER'S NEXT BILLING DATE
     *
     * @return Carbon
     */
    public function nextBillingDate()
    {
        return  $this->partner->next_billing_date ? Carbon::parse($this->partner->next_billing_date) : null;
    }

    /**
     * * FINDING PARTNER'S REMAINING BILLING DAY IN DAY
     *
     * @return int
     */
    public function remainingDay()
    {
        $next = $this->nextBillingDate();
        if ($next) {
            $today = Carbon::today();
            $diff = $today->diffInDays($next, false);
            return $diff > 0 ? $diff : 0;
        }
        return 0;
    }

    public function totalDaysOfUsage()
    {
        $next = $this->nextBillingDate();
        if ($next) {
            $last = Carbon::parse($this->partner->last_billed_date);
            return abs($last->diffInDays($next));
        }
        return 1;
    }

    /**
     * @return false|float|int
     * @throws InvalidPreviousSubscriptionRules
     */
    public function remainingCredit()
    {
        $remaining_credit = $this->usageLeft();
        return $remaining_credit < 0 ? 0 : round($remaining_credit, 2);
    }

    /**
     * @throws InvalidPreviousSubscriptionRules
     */
    private function usageLeft()
    {
        $remainingDay = $this->remainingDay();
        $perDayPrice = $this->currentPackagePerDayPrice();
        return round($remainingDay * $perDayPrice, 2);

    }

    /**
     * @return false|float
     * @throws InvalidPreviousSubscriptionRules
     */
    private function currentPackagePerDayPrice()
    {
        $subscriptionRules = $this->partner->subscription_rules;
        if (is_string($subscriptionRules)) $subscriptionRules = json_decode($subscriptionRules);
        $billing_type = $this->partner->billing_type;
        if (isset($subscriptionRules->subscription_fee))
            foreach ($subscriptionRules->subscription_fee as $fee)
                if ($fee->title == $billing_type) $value = $fee->price;
        if (!isset($value)) throw new InvalidPreviousSubscriptionRules();
        $total = $this->totalDaysOfUsage() ? : 1;
        return round(doubleval($value) / $total, 6);
    }


}
