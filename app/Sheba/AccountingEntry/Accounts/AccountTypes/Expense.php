<?php


namespace Sheba\AccountingEntry\Accounts\AccountTypes;


use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\EmiInterest;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\LoanService;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\PaymentLinkServiceCharge;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\Purchase;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SmsPurchase;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SubscriptionPurchase;

class Expense extends AccountTypes
{
    /**
     * @var SubscriptionPurchase
     */
    public $subscription_purchase;
    /** @var PaymentLinkServiceCharge */
    public $paymentLinkServiceCharge;
    /** @var EmiInterest */
    public $emiInterest;
    public $cost_of_good_sold;
    public $depreciation_cost;
    public $other_expense;
    /** @var Purchase */
    public $purchase;
    /** @var LoanService */
    public $loan_service;
    /** @var SmsPurchase $sms_purchase */
    public $sms_purchase;
}
