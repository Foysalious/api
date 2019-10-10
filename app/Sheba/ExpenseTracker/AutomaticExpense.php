<?php


namespace Sheba\ExpenseTracker;


class AutomaticExpense
{
    const SUBSCRIPTION_FEE = 'Subscription fee';
    const MARKETPLACE_ORDER = 'Commission of market place order';
    const PAYMENT_LINK = 'Commission of payment link';
    const TOP_UP = 'Top up purchase amount';
    const MOVIE_TICKET = 'Movie ticker purchase amount';
    const BUS_TICKET = 'Bus ticker purchase amount';
    const E_SHOP = 'Purchase from e-shop';
    const SMS = 'SMS marketing purchase';

    public static function heads()
    {
        return [
            self::SUBSCRIPTION_FEE, self::MARKETPLACE_ORDER, self::PAYMENT_LINK, self::TOP_UP, self::MOVIE_TICKET, self::BUS_TICKET, self::E_SHOP, self::SMS
        ];
    }
}
