<?php namespace App\Sheba\Customer\Listeners;

use App\Sheba\Customer\Events\PartnerPosCustomerEvent;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerJob;

class PartnerPosCustomerCreateListener
{
    public function __construct()
    {

    }

    public function handle(PartnerPosCustomerEvent $event)
    {
        dispatch(new AccountingCustomerJob($event));
    }
}
