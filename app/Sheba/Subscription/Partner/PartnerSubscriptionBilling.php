<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Tag;
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

    public function runUpfrontBilling()
    {
        $this->runningCycleNumber = 1;
        $this->partner->billing_start_date = $this->today;
        $package_price = $this->getSubscribedPackageDiscountedPrice();
        $this->billingDatabaseTransactions($package_price);
    }

    public function runSubscriptionBilling()
    {
        $this->runningCycleNumber = $this->calculateRunningBillingCycleNumber();
        $package_price = $this->getSubscribedPackageDiscountedPrice();
        $this->billingDatabaseTransactions($package_price);
    }

    public function runUpgradeBilling(PartnerSubscriptionPackage $old_package, PartnerSubscriptionPackage $new_package)
    {
        $billing_type = $this->partner->billing_type;
        $dayDiff = $this->partner->last_billed_date->diffInDays($this->today) + 1;
        $used_credit = $old_package->originalPricePerDay() * $dayDiff;
        $remaining_credit = $this->partner->last_billed_amount - $used_credit;
        $remaining_credit = $remaining_credit < 0 ? 0 : $remaining_credit;
        $package_price = ($new_package->originalPrice($billing_type) - $new_package->discountPrice($billing_type)) - $remaining_credit;
        $this->partner->billing_start_date = $this->today;
        $this->billingDatabaseTransactions($package_price);
    }


    private function calculateRunningBillingCycleNumber()
    {
        if ($this->partner->billing_type == "monthly") {
            $diff = $this->today->month - $this->partner->billing_start_date->month;
            $yearDiff = ($this->today->year - $this->partner->billing_start_date->year);
            return ($diff < 0 ? $diff + ($yearDiff * 12) : $diff) + 1;
        } elseif ($this->partner->billing_type == "yearly") {
            return ($this->today->year - $this->partner->billing_start_date->year) + 1;
        }
    }

    private function getSubscribedPackageDiscountedPrice()
    {
        $original_price = $this->partner->subscription->originalPrice($this->partner->billing_type);
        $discount = $this->calculateSubscribedPackageDiscount($this->runningCycleNumber, $original_price);
        return $original_price - $discount;
    }

    private function billingDatabaseTransactions($package_price)
    {
        DB::transaction(function () use ($package_price) {
            $this->partnerTransactionHandler->debit($package_price, $package_price . ' BDT has been deducted for subscription package', null, [$this->getSubscriptionTag()->id]);
            $this->partner->last_billed_date = $this->today;
            $this->partner->last_billed_amount = $package_price;
            $this->partner->update();
        });
    }

    private function calculateSubscribedPackageDiscount($running_bill_cycle_no, $original_price)
    {
        if ($this->partner->discount_id) {
            $subscription_discount = $this->partner->subscriptionDiscount;
            $discount_billing_cycles = json_decode($subscription_discount->applicable_billing_cycles);
            if (in_array($running_bill_cycle_no, $discount_billing_cycles)) {
                if ($subscription_discount->is_percentage) {
                    return $original_price * ($subscription_discount->amount / 100);
                } else {
                    return (double)$subscription_discount->amount;
                }
            }
        }
        return 0;
    }

    private function getSubscriptionTag()
    {
        return Tag::where('name', 'like', '%Subscription fee%')->where('taggable_type', 'App\\Models\\PartnerTransaction')->first();
    }
}