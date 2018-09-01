<?php

namespace Sheba\Subscription;


abstract class ShebaSubscriber
{
    public abstract function getPackage(SubscriptionPackage $package);

    public abstract function getPackages();

    public abstract function upgrade(SubscriptionPackage $package);

    public abstract function getBilling();
}