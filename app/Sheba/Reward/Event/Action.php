<?php namespace Sheba\Reward\Event;

use Sheba\Reward\Event;

abstract class Action extends Event
{
    abstract function isEligible(array $params);
}