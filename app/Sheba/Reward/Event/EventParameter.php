<?php namespace Sheba\Reward\Event;

abstract class EventParameter
{
    public $value;

    abstract public function validate();
}