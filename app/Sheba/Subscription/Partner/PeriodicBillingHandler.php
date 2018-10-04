<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use Carbon\Carbon;

class PeriodicBillingHandler
{
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
        };
    }

    public function hasBillingCycleEnded()
    {
        return $this->nextBillingDate()->isSameDay($this->today);
    }

    /**
     * FINDING PARTNER'S NEXT BILLING DATE
     *
     * @return Carbon
     */
    public function nextBillingDate()
    {
        $new_bill_date = '';
        $last_billed_date = $this->partner->last_billed_date;

        if ($this->partner->billing_type == "monthly") {
            if ($this->partner->billing_start_date->day <= $this->today->daysInMonth) {
                $new_bill_date = Carbon::createFromDate($this->today->year, $this->today->month, $this->partner->billing_start_date->day);
            } else {
                $new_bill_date = $this->today->copy()->endOfMonth();
            }
        } elseif ($this->partner->billing_type == "yearly") {
            $new_bill_date = $last_billed_date->copy()->addYear(1);
            /** @var $new_bill_date Carbon */
            if ($last_billed_date->isLeapYear() && $new_bill_date->month == 3 && $new_bill_date->day == 1) $new_bill_date->subDay(1);
            if ($new_bill_date->isLeapYear() && $last_billed_date->month == 2 && $last_billed_date->day == 28) $new_bill_date->addDay(1);
        }

        return $new_bill_date;
    }
}