<?php namespace Sheba\Reward\Event;

abstract class ActionEventParameter extends EventParameter
{
    abstract public function check(array $params);
}