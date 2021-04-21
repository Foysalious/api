<?php


namespace Sheba\AccountingEntry\Accounts;


use Sheba\AccountingEntry\Accounts\AccountTypes\Asset;

class Accounts extends RootAccounts
{
    public $income;
    public $expense;
    /** @var Asset */
    public $asset;
    public $liability;
    public $equity;
}
