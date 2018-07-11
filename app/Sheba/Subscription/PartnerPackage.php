<?php

namespace App\Sheba\Subscription;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;

class PartnerPackage implements Package
{
    private $package;
    private $partner;

    public function __construct(PartnerSubscriptionPackage $package, Partner $partner)
    {
        $this->package = ($package) instanceof PartnerSubscriptionPackage ? $package : PartnerSubscriptionPackage::find($package);
        $this->partner = $partner;

    }

    public function subscribe($type)
    {
        $this->partner->package_id = $this->package->id;
        $this->partner->billing_type = $type;
        $this->partner->update();
    }

    public function unsubscribe()
    {
    }
}