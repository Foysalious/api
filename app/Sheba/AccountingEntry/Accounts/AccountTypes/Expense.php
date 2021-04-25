<?php


namespace Sheba\AccountingEntry\Accounts\AccountTypes;


use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SubscriptionPurchase;

class Expense extends AccountTypes
{
    public $cost_of_good_sold;
    public $depreciation_cost;
    public $other_expense;
    public $purchase;
    /**
     * @var SubscriptionPurchase
     */
    public $subscription_purchase;
}
