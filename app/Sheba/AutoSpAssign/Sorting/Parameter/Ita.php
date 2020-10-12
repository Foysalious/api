<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class Ita extends Parameter
{
    protected function getWeight()
    {
        return config('auto_sp.weights.quality.ita');
    }

    protected function getValueForPartner()
    {
        return $this->partner->getIta();
    }
}