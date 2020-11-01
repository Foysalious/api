<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class Ota extends Parameter
{
    protected function getWeight()
    {
        return config('auto_sp.weights.quality.ota');
    }

    protected function getValueForPartner()
    {
        return $this->partner->getOta();
    }
}