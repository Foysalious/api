<?php


namespace Sheba\AccountingEntry\Accounts\AccountTypes;


use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\AccountReceivable;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Bank;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Cash;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\FixedAsset;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Inventory;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Sheba;

class Asset extends AccountTypes
{
    /** @var AccountReceivable */
    public $account_receivable;
    /** @var Bank */
    public $bank;
    /** @var Cash */
    public $cash;
    /** @var FixedAsset */
    public $fixed_asset;
    /** @var Inventory */
    public $inventory;
    /** @var Sheba */
    public $sheba;
    public $stock;

}
