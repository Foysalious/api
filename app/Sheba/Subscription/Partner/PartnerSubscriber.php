<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use Sheba\Subscription\Package;
use Sheba\Subscription\ShebaSubscriber;
use Sheba\Subscription\SubscriptionPackage;

class PartnerSubscriber extends ShebaSubscriber
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function getPackage(SubscriptionPackage $package = null)
    {
        return new PartnerPackage($package, $this->partner);
    }

    public function getPackages()
    {
        // return $model collection;
    }

    public function upgrade(SubscriptionPackage $package)
    {
        $this->getBilling()->runUpgradeBilling($package);
    }

    public function getBilling()
    {
        return (new PartnerSubscriptionBilling($this->partner));
    }

    public function periodicBillingHandler()
    {
        return (new PeriodicBillingHandler($this->partner));
    }

    public function canCreateResource($type)
    {
        if ($type == "Handyman") {
            return $this->partner->handymanResources()->count() < $this->resourceCap();
        } else {
            return true;
        }
    }

    public function rules()
    {
        return json_decode($this->partner->subscription->rules);
    }

    public function resourceCap()
    {
        return (int)$this->rules()->resource_cap->value;
    }

    public function commission()
    {
        return (double)$this->rules()->commission->value;
    }
}
