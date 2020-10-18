<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class ResourceAppUsage extends Parameter
{
    protected function getWeight()
    {
        return 20;
    }

    protected function getValueForPartner()
    {
        return $this->partner->getResourceAppUsageRatio();
    }
}