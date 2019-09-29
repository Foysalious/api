<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

abstract class BaseRule
{
    protected $prefix;

    public function setPrefix($previous, $prefix)
    {
        $this->prefix = $previous . $prefix;
        return $this;
    }

    public function __get($name)
    {
        $this->register($name, $this->getCurrentPrefix());
        if (is_string($this->$name)) return $this->getCurrentPrefix() . $this->$name; else return $this->$name;
    }

    protected function getCurrentPrefix()
    {
        return $this->prefix ? $this->prefix . "." : '';
    }

    abstract protected function register($name, $prefix);
}