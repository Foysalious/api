<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class ResourceAppUsage extends Parameter
{
    protected function getWeight()
    {
        return config('auto_sp.weights.quality.spro_app_usage');
    }

    protected function getValueForPartner()
    {
        return $this->partner->getResourceAppUsageRatio();
    }
}