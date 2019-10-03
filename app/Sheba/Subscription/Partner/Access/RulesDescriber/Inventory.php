<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property Warranty $WARRANTY
 */
class Inventory extends BaseRule
{
    protected $WARRANTY = "warranty";

    public function __construct()
    {
        $this->WARRANTY = new Warranty();
    }

    protected function register($name, $prefix)
    {
        if ($name == "WARRANTY") return $this->WARRANTY->setPrefix($prefix, 'warranty');
    }
}