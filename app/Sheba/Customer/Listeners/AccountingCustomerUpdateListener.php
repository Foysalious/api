<?php

namespace App\Sheba\Customer\Listeners;

use App\Sheba\Customer\Events\AccountingCustomerCreate;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerJob;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerUpdateJob;

class AccountingCustomerUpdateListener
{
    public function __construct()
    {

    }

    public function handle(AccountingCustomerCreate $event)
    {
        dispatch(new AccountingCustomerUpdateJob($event));
    }
}
