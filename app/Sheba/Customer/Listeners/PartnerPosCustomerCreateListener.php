<?php namespace App\Sheba\Customer\Listeners;

use App\Sheba\Customer\Events\PartnerPosCustomerCreatedEvent;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerJob;

class PartnerPosCustomerCreateListener
{
    public function __construct()
    {

    }

    public function handle(PartnerPosCustomerCreatedEvent $event)
    {
        dispatch(new AccountingCustomerJob($event));
    }
}
