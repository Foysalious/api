<?php namespace App\Sheba\Customer\Listeners;

use App\Sheba\Customer\Events\PartnerPosCustomerUpdatedEvent;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerUpdateJob;

class PartnerPosCustomerUpdateListener
{
    public function __construct()
    {

    }

    public function handle(PartnerPosCustomerUpdatedEvent $event)
    {
        dispatch(new AccountingCustomerUpdateJob($event));
    }
}
