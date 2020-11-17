<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class ComplainPercentage extends Parameter
{
    protected function getWeight()
    {
        return config('auto_sp.weights.quality.complain');
    }

    protected function getValueForPartner()
    {
        return $this->partner->getComplainRatio();
    }
}