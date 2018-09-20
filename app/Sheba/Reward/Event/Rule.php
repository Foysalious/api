<?php namespace Sheba\Reward\Event;

abstract class Rule
{
    protected $rule;

    public function __construct($rule)
    {
        $this->rule = is_string($rule) ? json_decode($rule) : $rule;
        $this->makeParamClasses();
        $this->validate();
        $this->setValues();
    }

    abstract public function makeParamClasses();

    abstract public function validate();

    abstract public function setValues();
}