<?php namespace Sheba\ExpenseTracker;

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
        return [
            self::MARKET_PLACE,
            self::POS,
            self::TOP_UP,
            self::MOVIE_TICKET,
            self::BUS_TICKET,
            self::PAYMENT_LINK
        ];
    }
}
