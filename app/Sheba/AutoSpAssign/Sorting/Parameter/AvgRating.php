<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class AvgRating extends Parameter
{
    protected function getWeight()
    {
        return config('auto_sp.weights.quality.avg_rating');
    }

    protected function getValueForPartner()
    {
        if ($this->partner->getAvgRating() == $this->minValue) return 0;
        return ($this->partner->getAvgRating() - $this->minValue) / ($this->maxValue - $this->minValue) * 100;
    }
}