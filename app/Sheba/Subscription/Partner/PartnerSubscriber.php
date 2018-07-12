<?php

namespace App\Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Sheba\Subscription\Package;
use App\Sheba\Subscription\ShebaSubscriber;

class PartnerSubscriber extends ShebaSubscriber
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function getPackage(Package $package = null)
    {
        $package = $package ? (($package) instanceof PartnerSubscriptionPackage ? $package : PartnerSubscriptionPackage::find($package)) : $this->partner->subscription;
        return new PartnerPackage($package, $this->partner);
    }

    public function getPackages()
    {
        // return $model collection;
    }

    public function upgrade(Package $package)
    {
    }

    public function getBilling()
    {
        return (new PartnerSubscriptionBilling($this->partner));
    }
}
