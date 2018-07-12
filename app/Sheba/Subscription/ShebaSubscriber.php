<?php

namespace Sheba\Subscription;


abstract class ShebaSubscriber
{
    public abstract function getPackage(Package $package);

    public abstract function getPackages();

    public abstract function upgrade(Package $package);

    public abstract function getBilling();
}