<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


class PackageScore extends Parameter
{

    protected function getWeight()
    {
        return 5;
    }

    protected function getValueForPartner()
    {
        return 0;
    }
}