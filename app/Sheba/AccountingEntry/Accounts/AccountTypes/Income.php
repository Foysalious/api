<?php

namespace Sheba\AccountingEntry\Accounts\AccountTypes;

use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Income\IncomeFromEmi;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Income\IncomeFromPaymentLink;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Income\Reffer;

class Income extends AccountTypes
{
    /** @var Reffer */
    public $reffer;
    /** @var IncomeFromPaymentLink */
    public $incomeFromPaymentLink;
    /** @var IncomeFromEmi */
    public $incomeFromEmi;
    public $other_income;
    public $sales;
    public $sales_bechabikri;
    public $sales_emi;
    public $sales_payment_link;
    public $sales_sheba_xyz;
    public $sales_ticket;
    public $sales_top_up;
    public $reward;
}
