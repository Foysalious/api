<?php

namespace Sheba\Subscription;


abstract class ShebaSubscriber
{
    public abstract function getPackage(SubscriptionPackage $package);

    public abstract function getPackages();

    public abstract function upgrade(SubscriptionPackage $package, $billing_type);

    public abstract function getBilling();
}