<?php namespace Sheba\ExpenseTracker;

use ReflectionClass;

class AutomaticExpense
{
    const SUBSCRIPTION_FEE = 'Subscription fee';
    const MARKET_PLACE_ORDER = 'Commission of market place order';
    const PAYMENT_LINK = 'Commission of payment link';
    const TOP_UP = 'Top up purchase amount';
    const MOVIE_TICKET = 'Movie ticker purchase amount';
    const BUS_TICKET = 'Bus ticker purchase amount';
    const E_SHOP = 'Purchase from e-shop';
    const SMS = 'SMS marketing purchase';

    public static function heads()
    {
        return array_values((new ReflectionClass(__CLASS__))->getConstants());
    }
}
