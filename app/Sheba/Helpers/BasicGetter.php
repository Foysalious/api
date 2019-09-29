<?php namespace Sheba\Helpers;

trait BasicGetter
{
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}