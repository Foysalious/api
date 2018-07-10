<?php

namespace App\Sheba\Subscription;


use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;

class PartnerSubscriber extends ShebaSubscriber
{
    private $partner;

    public function __construct($partner)
    {
        $this->partner = ($partner) instanceof Partner ? $partner : Partner::find($partner);
    }

    public function getPackage(Package $package)
    {
        return new PartnerPackage($package);
    }

    public function getPackages()
    {
        // return $model collection;
    }

}
