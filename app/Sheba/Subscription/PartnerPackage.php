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

    public function subscribe()
    {
    }

    public function unsubscribe()
    {
    }
}