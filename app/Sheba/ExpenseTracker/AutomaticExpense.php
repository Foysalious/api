<?php namespace Sheba\ExpenseTracker;

use ReflectionClass;

class AutomaticExpense
{
    const SUBSCRIPTION_FEE   = 'Subscription fee';
    const MARKET_PLACE_ORDER = 'Marketplace Order Commission';
    const PAYMENT_LINK       = 'Payment Link Commission';
    const TOP_UP             = 'Mobile Recharge Purchase';
    const MOVIE_TICKET       = 'Movie ticker purchase';
    const BUS_TICKET         = 'Bus Ticket Purchase';
    const E_SHOP             = 'e-shop Purchase';
    const SMS                = 'SMS Purchase';
    const OTHER_EXPENSES     = 'Other Expenses';
    const DUE_TRACKER        = 'Due Tracker';
    const SHEBA_ACCOUNT      = 'Sheba Account';
    const GENERAL_REFUNDS    = 'General Refunds';

    public static function heads()
    {
        return array_values((new ReflectionClass(__CLASS__))->getConstants());
    }
}
