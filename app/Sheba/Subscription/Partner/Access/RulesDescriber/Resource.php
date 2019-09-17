<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;


class Resource extends BaseRule
{
    protected $TYPE;

    public function __construct()
    {
        $this->TYPE = new ResourceType();
    }

    protected function register($name, $prefix)
    {
        // TODO: Implement register() method.
        if ($name == "TYPE") return $this->TYPE->setPrefix($prefix, 'type');
    }
}
