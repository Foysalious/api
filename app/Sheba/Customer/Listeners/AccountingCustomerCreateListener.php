<?php namespace App\Sheba\Customer\Listeners;

use App\Sheba\Customer\Events\AccountingCustomerCreate;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerJob;

class AccountingCustomerCreateListener
{
    public function __construct()
    {

    }

    public function handle(AccountingCustomerCreate $event)
    {
        dispatch(new AccountingCustomerJob($event));
    }
}
