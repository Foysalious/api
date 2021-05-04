<?php


namespace Sheba\AccountingEntry\Accounts\AccountTypes;


use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\Purchase;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SubscriptionPurchase;

class Expense extends AccountTypes
{
    /**
     * @var SubscriptionPurchase
     */
    public $subscription_purchase;
    public $cost_of_good_sold;
    public $depreciation_cost;
    public $other_expense;
    /** @var Purchase */
    public $purchase;
    public $loan_service;
    public $sms_purchase;
}
