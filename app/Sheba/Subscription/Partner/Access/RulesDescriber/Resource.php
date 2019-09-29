<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property ResourceType $TYPE
 */
class Resource extends BaseRule
{
    protected $TYPE;

    public function __construct()
    {
        $this->TYPE = new ResourceType();
    }

    protected function register($name, $prefix)
    {
        if ($name == "TYPE") return $this->TYPE->setPrefix($prefix, 'type');
    }
}
