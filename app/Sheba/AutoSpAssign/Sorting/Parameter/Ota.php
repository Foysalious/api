<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class Ota extends Parameter
{
    protected function getWeight()
    {
        return 15;
    }

    protected function getValueForPartner()
    {
        return $this->partner->getOta();
    }
}