<?php

namespace App\Sheba\Subscription;

use App\Models\Partner;

class PartnerPackage implements Package
{
    private $package;
    private $partner;

    public function __construct(PartnerPackage $package, Partner $partner)
    {
        $this->package = $package;
        $this->partner = $partner;
    }

    public function subscribe($billing_type)
    {
        $this->partner->package_id = $this->package->id;
        $this->partner->billing_type = $billing_type;
        $this->partner->update();
    }

    public function unsubscribe()
    {

    }
}