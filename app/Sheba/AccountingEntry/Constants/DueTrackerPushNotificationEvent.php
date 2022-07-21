<?php

namespace App\Sheba\AccountingEntry\Constants;

use Sheba\Helpers\ConstGetter;

class DueTrackerPushNotificationEvent
{
    use ConstGetter;

    const DUE_TRACKER_CUSTOMER = 'due_tracker_customer';
    const DUE_TRACKER_SUPPLIER = 'due_tracker_supplier';
}