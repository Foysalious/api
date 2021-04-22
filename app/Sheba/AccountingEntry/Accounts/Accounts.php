<?php


namespace Sheba\AccountingEntry\Accounts;


use Sheba\AccountingEntry\Accounts\AccountTypes\Asset;
use Sheba\AccountingEntry\Accounts\AccountTypes\Expense;

class Accounts extends RootAccounts
{
    public $income;
    /**
     * @var Expense
     */
    public $expense;
    /** @var Asset */
    public $asset;
    public $liability;
    public $equity;
}
