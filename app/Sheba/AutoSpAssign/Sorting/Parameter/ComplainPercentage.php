<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class ComplainPercentage extends Parameter
{
    protected function getWeight()
    {
        return 10;
    }

    protected function getValueForPartner()
    {
        return $this->partner->getComplainRatio();
    }
}