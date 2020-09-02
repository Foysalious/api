<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class PackageScore extends Parameter
{

    protected function getWeight()
    {
        return 5;
    }

    protected function getValueForPartner()
    {
        return $this->getValue($this->partner->getPackageId());
    }

    private function getValue($package_id)
    {
        if ($package_id == 5) return 100;
        if ($package_id == 4) return 90;
        if ($package_id == 3) return 80;
        if ($package_id == 8) return 70;
        if ($package_id == 9) return 60;
        if ($package_id == 6) return 50;
        if ($package_id == 7) return 40;
        return 30;
    }
}