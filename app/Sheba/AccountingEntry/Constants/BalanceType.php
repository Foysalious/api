<?php

namespace App\Sheba\AccountingEntry\Constants;

use Sheba\Helpers\ConstGetter;

class BalanceType
{
    use ConstGetter;

    const RECEIVABLE = 'receivable';
    const PAYABLE = 'payable';
}