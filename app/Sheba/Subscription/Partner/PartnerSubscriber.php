<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use Sheba\Subscription\ShebaSubscriber;
use Sheba\Subscription\SubscriptionPackage;
use DB;

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

    public function upgrade(SubscriptionPackage $package, $billing_type)
    {
        $old_package = $this->partner->subscription;
        DB::transaction(function () use ($old_package, $package, $billing_type) {
            $this->getPackage($package)->subscribe($billing_type);
            $this->getBilling()->runUpgradeBilling($old_package, $package);
        });

    }

    public function getBilling()
    {
        return (new PartnerSubscriptionBilling($this->partner));
    }

    public function periodicBillingHandler()
    {
        return (new PeriodicBillingHandler($this->partner));
    }

    public function canCreateResource($types)
    {
        return in_array(constants('RESOURCE_TYPES')['Handyman'], $types) ? $this->partner->handymanResources()->count() < $this->resourceCap() : true;
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
