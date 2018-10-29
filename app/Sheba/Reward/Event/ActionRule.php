<?php namespace Sheba\Reward\Event;

abstract class ActionRule extends Rule
{
    abstract public function check(array $params);
}