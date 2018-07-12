<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;

class PeriodicBillingHandler
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function run()
    {
        if ($this->hasBillingCycleEnded()) $this->partner->runSubscriptionBilling();
    }

    public function hasBillingCycleEnded()
    {
        $last_billed_date = $this->partner->last_billed_date;
        if ($this->partner->billing_type == "monthly") {
            $new_bill_date = $last_billed_date->copy()->addMonthNoOverflow(1);
            return $new_bill_date->isToday();
        } elseif ($this->partner->billing_type == "yearly") {
            $new_bill_date = $last_billed_date->copy()->addYear(1);
            if ($last_billed_date->isLeapYear() && $new_bill_date->month == 3 && $new_bill_date->day == 1) $new_bill_date->subDay(1);
            if ($new_bill_date->isLeapYear() && $last_billed_date->month == 2 && $last_billed_date->day == 28) $new_bill_date->addDay(1);
            return $new_bill_date->isToday();
        }
    }
}