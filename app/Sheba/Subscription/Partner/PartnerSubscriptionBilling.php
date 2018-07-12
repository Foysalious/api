<?php

namespace App\Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Carbon\Carbon;
use DB;

class PartnerSubscriptionBilling
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function runSubscriptionBilling()
    {
        $package_price = $this->getPackagePrice();
        $day_difference = Carbon::today()->diffInDays(Carbon::parse($this->partner->billing_start_date));
        $days = $this->getBillingTypeDays();
        $running_billing_cycle_no = $this->calculateRunningBillingCycleNumber($day_difference, $days);
        $discount = $this->calculateSubscriptionDiscount($running_billing_cycle_no, $package_price);
        $package_price -= $discount;
        DB::transaction(function () use ($package_price) {
            $this->deductWallet($package_price);
            $this->createWalletDeductLog($package_price);
            $this->setLastBillingDate(date('Y-m-d'));
        });
    }

    private function getBillingTypeDays()
    {
        if ($this->partner->billing_type == "monthly") {
            return 30;
        } elseif ($this->partner->billing_type == "yearly") {
            return 365;
        }
    }

    public function hasBillingCycleEnded($day_difference, $days)
    {
        return ($day_difference - 1) % $days == 0 ? 1 : 0;
    }

    private function calculateRunningBillingCycleNumber($day_difference, $days)
    {
        return ceil($day_difference / $days);
    }

    private function setLastBillingDate($date)
    {
        $this->partner->last_billed_date = $date;
        $this->partner->update();
    }

    public function runUpfrontBilling()
    {
        $package_price = $this->getPackagePrice();
        $discount = $this->calculateSubscriptionDiscount(1, $package_price);
        $package_price -= $discount;
        DB::transaction(function () use ($package_price) {
            $this->deductWallet($package_price);
            $this->createWalletDeductLog($package_price);
            $this->setBillingStartDate(date('Y-m-d'));
        });
    }

    private function getPackagePrice()
    {
        $partner_subscription = $this->partner->subscription;
        $billing_type = $this->partner->billing_type;
        $package_price = (double)json_decode($partner_subscription->rules)->fee->$billing_type->value;
        return $package_price;
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

    private function deductWallet($amount)
    {
        $this->partner->wallet -= $amount;
        $this->partner->update();
    }

    private function createWalletDeductLog($amount)
    {
        $partner_transaction = new PartnerTransaction();
        $partner_transaction->partner_id = $this->partner->id;
        $partner_transaction->amount = $amount;
        $partner_transaction->type = "Debit";
        $partner_transaction->log = "$amount BDT has been deducted for subscription package";
        $partner_transaction->created_at = Carbon::now();
        $partner_transaction->save();
    }

    private function setBillingStartDate($date)
    {
        $this->partner->billing_start_date = $date;
        $this->partner->update();
    }

}