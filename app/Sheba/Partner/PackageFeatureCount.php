<?php namespace App\Sheba\Partner;

class PackageFeatureCount
{
    public function topupCurrentCount(): int
    {
        return 10;
    }

    public function smsCurrentCount(): int
    {
        return 10;
    }

    public function deliveryCurrentCount(): int
    {
        return 10;
    }
}