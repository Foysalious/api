<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class Impression extends Parameter
{

    protected function getWeight()
    {
        return config('auto_sp.weights.impression');
    }

    protected function getValueForPartner()
    {
        return $this->partner->getImpressionCount();
    }
}