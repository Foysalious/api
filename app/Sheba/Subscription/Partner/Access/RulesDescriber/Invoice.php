<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property string $PRINT
 * @property string $DOWNLOAD
 */
class Invoice extends BaseRule
{
    protected $PRINT = "print";
    protected $DOWNLOAD = "download";

    protected function register($name, $prefix)
    {
        // TODO: Implement register() method.
    }
}