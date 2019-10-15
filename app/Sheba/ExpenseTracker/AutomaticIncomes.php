<?php namespace Sheba\ExpenseTracker;

use ReflectionClass;

class AutomaticIncomes
{
    const MARKET_PLACE = 'Marketplace sales';
    const POS = 'POS sales';
    const TOP_UP = 'Top up (Top-up full amount)';
    const MOVIE_TICKET = 'Movie ticket sale';
    const BUS_TICKET = 'Bus ticker sale';
    const PAYMENT_LINK = 'Payment link';

    public static function heads()
    {
        return array_values((new ReflectionClass(__CLASS__))->getConstants());
    }
}
