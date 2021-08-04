<?php


namespace Sheba\AccountingEntry\Accounts;


use Sheba\AccountingEntry\Accounts\AccountTypes\Asset;
use Sheba\AccountingEntry\Accounts\AccountTypes\Expense;
use Sheba\AccountingEntry\Accounts\AccountTypes\Income;

class Accounts extends RootAccounts
{
    /** @var Income */
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
