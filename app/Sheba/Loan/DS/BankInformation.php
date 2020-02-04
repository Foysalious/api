<?php

namespace Sheba\Loan\DS;
class BankInformation
{
    use ReflectionArray;
    protected $acc_name;
    protected $acc_no;
    protected $bank_name;
    protected $branch_name;
    protected $acc_type;
    protected $period;
    protected $debit_sum;
    protected $credit_sum;
    protected $monthly_avg_credit_sum;
}
