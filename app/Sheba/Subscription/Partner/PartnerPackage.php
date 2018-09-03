<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use Sheba\Subscription\Package;

class PartnerPackage implements Package
{
    private $package;
    private $partner;

    public function __construct(PartnerSubscriptionPackage $package, Partner $partner)
    {
        $this->package = $package;
        $this->partner = $partner;
    }

    public function subscribe($billing_type)
    {
        $this->partner->package_id = $this->package->id;
        $this->partner->billing_type = $billing_type;
        $running_discount = $this->package->runningDiscount($billing_type);
        $this->partner->discount_id = $running_discount ? $running_discount->id : null;
        $this->partner->requested_billing_type = null;
        $this->partner->update();
    }

    public function unsubscribe()
    {

    }
}