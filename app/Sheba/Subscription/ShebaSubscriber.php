<?php namespace Sheba\Subscription;

use App\Models\PartnerSubscriptionUpdateRequest;

abstract class ShebaSubscriber
{
    abstract public function getPackage(SubscriptionPackage $package);

    abstract public function getPackages();

    abstract public function upgrade(SubscriptionPackage $package, PartnerSubscriptionUpdateRequest $update_request);

    abstract public function getBilling();
}