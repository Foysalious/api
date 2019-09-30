<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property string $ALERT
 * @property string $LEDGER
 */
class Due extends BaseRule
{
    protected $ALERT = "alert";
    protected $LEDGER = "ledger";

    protected function register($name, $prefix)
    {
        // TODO: Implement register() method.
    }
}