<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;

class Commission extends Parameter
{

    protected function getWeight()
    {
        return config('auto_sp.weights.quality.commission');
    }

    protected function getValueForPartner()
    {
        $this->partner->categroyCommission($this->categoryId);
    }
}
