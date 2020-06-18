<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class AvgRating extends Parameter
{
    protected function getWeight()
    {
        return 10;
    }

    protected function getValueForPartner()
    {
        return $this->partner->getAvgRating();
    }
}