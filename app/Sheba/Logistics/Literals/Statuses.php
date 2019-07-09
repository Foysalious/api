<?php namespace Sheba\Logistics\Literals;

use Carbon\Carbon;
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

    /**
     * @param $status
     * @return string
     * @throws \Exception
     */
    public static function getReadable($status)
    {
        switch ($status) {
            case 'pending':
                return self::DELIVERY_SCHEDULED;
            case 'search_started':
                return self::RIDER_SEARCHING;
            case 'rider_not_found':
                return self::RIDER_NOT_FOUND;
            case 'assigned':
                return self::RIDER_ASSIGNED;
            case 'picked':
                return self::ON_THE_WAY;
            case 'dropped':
                return self::DELIVERED;
            case 'cancelled':
                return self::DELIVERY_CANCELLED;
            default:
                throw new \Exception('Invalid Status Exception');
                break;

        }
    }
}