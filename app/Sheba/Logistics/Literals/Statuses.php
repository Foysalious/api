<?php namespace Sheba\Logistics\Literals;

use Carbon\Carbon;
use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const PENDING = 'pending';
    const SEARCH_STARTED = 'search_started';
    const RIDER_NOT_FOUND = 'rider_not_found';
    const ASSIGNED = 'assigned';
    const PICKED = 'picked';
    const DROPPED = 'dropped';
    const CANCELLED = 'cancelled';

    /**
     * @param $status
     * @return string
     * @throws \Exception
     */
    public static function getReadable($status)
    {
        switch ($status) {
            case self::PENDING: return 'Delivery Scheduled';
            case self::SEARCH_STARTED: return 'Rider Searching';
            case self::RIDER_NOT_FOUND: return 'Rider Not Found';
            case self::ASSIGNED: return 'Rider Assigned';
            case self::PICKED: return 'On The Way';
            case self::DROPPED: return 'Delivered';
            case self::CANCELLED: return 'Cancelled';
            default: throw new \Exception('Invalid Status Exception');
        }
    }

    public static function isReschedulable($status)
    {
        return self::isPickUpDataChangeable($status);
    }

    public static function isPickUpDataChangeable($status)
    {
        return self::hasStarted($status);
    }

    public static function hasStarted($status)
    {
        return in_array($status, [self::PENDING, self::RIDER_NOT_FOUND/*, self::SEARCH_STARTED*/]);
    }
}