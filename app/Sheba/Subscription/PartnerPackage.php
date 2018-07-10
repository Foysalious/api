<?php

namespace App\Sheba\Subscription;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;

class PartnerPackage implements Package
{
    private $package;

    public function __construct(PartnerSubscriptionPackage $package)
    {
        $this->package = ($package) instanceof PartnerSubscriptionPackage ? $package : PartnerSubscriptionPackage::find($package);

    }

    public function subscribe()
    {
        // TODO: Implement subscribe() method.
    }

    public function unsubscribe()
    {
        // TODO: Implement unsubscribe() method.
    }
}