<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use Carbon\Carbon;
use DB;
use Sheba\PartnerWallet\PartnerTransactionHandler;

class PartnerSubscriptionBilling
{
    private $partner;
    private $runningCycleNumber;
    private $partnerTransactionHandler;
    private $today;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
        $this->partnerTransactionHandler = new PartnerTransactionHandler($this->partner);
        $this->today = Carbon::today();
    }

    public function runSubscriptionBilling()
    {
        $this->runningCycleNumber = $this->calculateRunningBillingCycleNumber();
        $this->billingDatabaseTransactions();
    }

    private function calculateRunningBillingCycleNumber()
    {
        if ($this->partner->billing_type == "monthly") {
            $diff = $this->today->month - $this->partner->billing_start_date->month;
            return ($diff < 0 ? $diff + 12 : $diff) + 1;

        } elseif ($this->partner->billing_type == "yearly") {
            return ($this->today->year - $this->partner->billing_start_date->year) + 1;
        }
    }

    private function billingDatabaseTransactions()
    {
        $package_price = $this->getPackagePrice();
        $discount = $this->calculateSubscriptionDiscount($this->runningCycleNumber, $package_price);
        $package_price -= $discount;
        DB::transaction(function () use ($package_price) {
            $this->partnerTransactionHandler->debit($package_price, $package_price . ' BDT has been deducted for subscription package');
            $this->partner->last_billed_date = $this->today;
            $this->partner->update();
        });
    }

    private function getPackagePrice()
    {
        $partner_subscription = $this->partner->subscription;
        $billing_type = $this->partner->billing_type;
        $package_price = (double)json_decode($partner_subscription->rules)->fee->$billing_type->value;
        return $package_price;
    }

    public function runUpfrontBilling()
    {
        $this->runningCycleNumber = 1;
        $this->partner->billing_start_date = $this->today;
        $this->billingDatabaseTransactions();
    }


    private function calculateSubscriptionDiscount($running_bill_cycle_no, $package_price)
    {
        if ($this->partner->discount_id) {
            $subscription_discount = $this->partner->subscriptionDiscount;
            $discount_billing_cycles = json_decode($subscription_discount->applicable_billing_cycles);
            if (in_array($running_bill_cycle_no, $discount_billing_cycles)) {
                if ($subscription_discount->is_percentage) {
                    return $package_price * ($subscription_discount->amount / 100);
                } else {
                    return (double)$subscription_discount->amount;
                }
            }
        }
        return 0;
    }
}