<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


abstract class Parameter
{
    public function getScore($value): double
    {
        return $this->getWeight() * $value;
    }

    abstract protected function getWeight();

}