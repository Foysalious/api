<?php namespace Sheba\Logistics\Literals;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const DELIVERY_SCHEDULED = 'Delivery Scheduled';
    const RIDER_SEARCHING = 'Rider Searching';
    const RIDER_NOT_FOUND = 'Rider Not Found';
    const RIDER_ASSIGNED = 'Rider Assigned';
    const ON_THE_WAY = 'On The Way';
    const DELIVERED = 'Delivered';
    const DELIVERY_CANCELLED = 'Cancelled';
}