<?php

namespace App\Sheba\Subscription;


use App\Models\Partner;

class PartnerSubscriber extends ShebaSubscriber
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function getPackage(Package $package)
    {
        return new PartnerPackage($package, $this->partner);
    }

    public function getPackages()
    {
        // return $model collection;
    }
}
