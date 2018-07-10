<?php

namespace App\Sheba\Subscription;


abstract class ShebaSubscriber
{
    public abstract function getPackage(Package $package);

    public abstract function getPackages();

}