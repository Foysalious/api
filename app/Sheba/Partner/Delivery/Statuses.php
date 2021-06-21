<?php namespace App\Sheba\Partner\Delivery;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const CREATED = 'Created';
    const PICKED_UP = 'Picked up';
    const ON_ROUTE = 'On Route';
    const RECEIVED_AT_POINT = 'ReceivedAtPoint';
    const PICKED_FOR_DELIVERY = 'PickedForDelivery';
    const DELIVERED = 'Delivered';
    const RETURNED = 'Returned';
    const PARTIAL = 'Partial';
    const ON_HOLD_SCHEDULE = 'onHoldSchedule';
    const CLOSE = 'Close';
}