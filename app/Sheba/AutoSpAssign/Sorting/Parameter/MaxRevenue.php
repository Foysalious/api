<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class MaxRevenue extends Parameter
{
    protected function getWeight()
    {
        return 15;
    }

    protected function getValueForPartner()
    {
        return $this->partner->getMaxRevenue();
    }
}