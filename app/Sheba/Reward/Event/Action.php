<?php namespace Sheba\Reward\Event;

use Sheba\Reward\Event;

abstract class Action extends Event
{
    protected $params;

    abstract function isEligible();

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }
}