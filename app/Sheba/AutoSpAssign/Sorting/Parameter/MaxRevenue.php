<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class MaxRevenue extends Parameter
{
    protected function getWeight()
    {
        return 15;
    }

    protected function getValueForPartner()
    {
        if ($this->partner->getMaxRevenue() == $this->minValue) return 0;
        return ($this->partner->getMaxRevenue() - $this->minValue) / ($this->maxValue - $this->minValue) * 100;
    }
}