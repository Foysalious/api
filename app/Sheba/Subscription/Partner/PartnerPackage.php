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

    public function subscribe($billing_type, $discount_id)
    {
        $this->partner->package_id = $this->package->id;
        $this->partner->billing_type = $billing_type;
        $this->partner->discount_id = $discount_id;
        $this->partner->update();

        $this->upgradeCommission($this->package->commission);
    }

    public function unsubscribe()
    {

    }

    public function upgradeCommission($commission)
    {
        foreach ($this->partner->categories as $category) {
            $category->pivot->commission = $commission;
            $category->pivot->update();
        }
    }
}