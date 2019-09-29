<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property string $TOPUP
 * @property string $MOVIE
 * @property string $TRANSPORT
 * @property string $UTILITY
 */
class ExtraEarning extends BaseRule
{
    protected $TOPUP = "topup";
    protected $MOVIE = "movie";
    protected $TRANSPORT = "transport";
    protected $UTILITY = "utility";

    protected function register($name, $prefix)
    {
        // TODO: Implement register() method.
    }
}