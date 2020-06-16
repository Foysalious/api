<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
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
        $this->today   = Carbon::today();
    }

    public function run()
    {
        if ($this->hasBillingCycleEnded()) {
            $this->partner->runSubscriptionBilling();
        }
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
        $new_bill_date    = '';
        $last_billed_date = $this->partner->last_billed_date;

        if ($this->partner->billing_type == BillingType::MONTHLY) {
            $next_billed_date_month = (($last_billed_date->month + 1) % 12) ?: 12;
            $next_billed_date_year  = $last_billed_date->year + ($last_billed_date->month == 12);
            $new_bill_date          = Carbon::createFromDate($next_billed_date_year, $next_billed_date_month, 1);

            if ($this->partner->billing_start_date->day <= $new_bill_date->daysInMonth) {
                $new_bill_date->day($this->partner->billing_start_date->day);
            } else {
                $new_bill_date->day = $new_bill_date->daysInMonth;
            }
        } elseif ($this->partner->billing_type == BillingType::HALF_YEARLY) {
            $new_bill_date = $last_billed_date->copy()->addMonths(6);
        } elseif ($this->partner->billing_type == BillingType::YEARLY) {
            $new_bill_date = $last_billed_date->copy()->addYear(1);
            /** @var $new_bill_date Carbon */
            if ($last_billed_date->isLeapYear() && $new_bill_date->month == 3 && $new_bill_date->day == 1) $new_bill_date->subDay(1);
            if ($new_bill_date->isLeapYear() && $last_billed_date->month == 2 && $last_billed_date->day == 28) $new_bill_date->addDay(1);
        }

        return $new_bill_date;
    }

    /**
     * * FINDING PARTNER'S REMAINING BILLING DAY IN DAY
     *
     * @return int
     */
    public function remainingDay()
    {
        $next  = $this->nextBillingDate();
        $today = Carbon::today();
        $diff  = $today->diffInDays($next, false);
        return $diff > 0 ? $diff : 0;
    }

    public function totalDaysOfUsage()
    {
        $next = $this->nextBillingDate();
        $last = Carbon::parse($this->partner->last_billed_date);
        return abs($last->diffInDays($next));
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
        $perDayPrice  = $this->currentPackagePerDayPrice();
        return round($remainingDay * $perDayPrice, 2);

    }

    /**
     * @return false|float
     * @throws InvalidPreviousSubscriptionRules
     */
    private function currentPackagePerDayPrice()
    {
        $subscriptionRules = $this->partner->subscription_rules;
        $billing_type      = $this->partner->billing_type;
        if (!isset($subscriptionRules->fee->$billing_type->value)) {
            throw new InvalidPreviousSubscriptionRules();
        }
        $total = $this->totalDaysOfUsage();
        return round($subscriptionRules->fee->$billing_type->value / $total, 2);
    }


}
