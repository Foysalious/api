<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class Ita extends Parameter
{
    protected function getWeight()
    {
        return 25;
    }

    protected function getValueForPartner()
    {
        return $this->partner->getIta();
    }
}