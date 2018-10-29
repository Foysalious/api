<?php namespace Sheba\Subscription;

use App\Models\PartnerSubscriptionUpdateRequest;

abstract class ShebaSubscriber
{
    public abstract function getPackage(SubscriptionPackage $package);

    public abstract function getPackages();

    public abstract function upgrade(SubscriptionPackage $package, PartnerSubscriptionUpdateRequest $update_request);

    public abstract function getBilling();
}