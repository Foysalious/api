<?php namespace Sheba\Jobs;

use Sheba\Helpers\ConstGetter;

class JobStatuses
{
    use ConstGetter;

    const PENDING = "Pending";
    const DECLINED = "Declined";
    const NOT_RESPONDED = "Not Responded";
    const ACCEPTED = "Accepted";
    const SCHEDULE_DUE = "Schedule Due";
    const PROCESS = "Process";
    const SERVE_DUE = "Serve Due";
    const SERVED = "Served";
    const SERVED_AND_DUE = "Served And Due";
    const CANCELLED = "Cancelled";

    public static function getClosed()
    {
        return [self::SERVED, self::CANCELLED];
    }

    public static function getActuals()
    {
        return self::getAllWithout(self::SERVED_AND_DUE);
    }

    public static function getAcceptable()
    {
        return [self::PENDING, self::NOT_RESPONDED];
    }

    public static function getOngoing()
    {
        return [self::ACCEPTED, self::SCHEDULE_DUE, self::PROCESS, self::SERVE_DUE, self::SERVED];
    }

    public static function getOngoingWithoutServed()
    {
        return [self::ACCEPTED, self::SCHEDULE_DUE, self::PROCESS, self::SERVE_DUE];
    }

    public static function getClosedString($glue = ",")
    {
        return self::getString('closed', $glue);
    }

    public static function getOpen()
    {
        return array_diff(self::get(), self::getClosed());
    }

    public static function getOpenString($glue = ",")
    {
        return self::getString('open', $glue);
    }

    public static function getString($statuses = "all", $glue = ",")
    {
        $statuses = $statuses == "all" ? self::get() : ($statuses == "open" ? self::getOpen() : self::getClosed());
        return implode($glue, $statuses);
    }

    public static function canChange($from, $to)
    {
        if (!in_array($to, self::get())) return false;
        if (!self::isChangeable($to)) return false;
        if ($to == self::SERVED && !self::isServeable($from)) return false;
        if ($to == self::PROCESS && !self::isProcessable($from)) return false;
        if ($to == self::ACCEPTED && !self::isAcceptable($from)) return false;
        if ($to == self::DECLINED && !self::isDeclineable($from)) return false;
        if ($to == self::CANCELLED && !self::isCancelable($from)) return false;
        return true;
    }

    public static function isOngoing($status)
    {
        return in_array($status, self::getOngoing());
    }

    public static function isChangeable($to)
    {
        return in_array($to, [self::SERVED, self::PROCESS, self::ACCEPTED, self::DECLINED, self::CANCELLED]);
    }

    public static function isAcceptable($from)
    {
        return in_array($from, [self::PENDING, self::NOT_RESPONDED]);
    }

    public static function isProcessable($from)
    {
        return in_array($from, [self::ACCEPTED, self::SCHEDULE_DUE]);
    }

    public static function isServeable($from)
    {
        return in_array($from, [self::PROCESS, self::SERVE_DUE]);
    }

    public static function isDeclineable($from)
    {
        return in_array($from, [self::PENDING, self::NOT_RESPONDED, self::SCHEDULE_DUE]);
    }

    public static function isCancelable($from)
    {
        return !in_array($from, [self::SERVED, self::CANCELLED]);
    }

    public static function shouldAcceptOnResourceChange($status)
    {
        return in_array($status, [self::PENDING, self::NOT_RESPONDED]);
    }

    public static function canBeNotResponded($status)
    {
        return in_array($status, [self::PENDING]);
    }

    public static function canBeScheduleDue($status)
    {
        return in_array($status, [self::ACCEPTED, self::SCHEDULE_DUE]);
    }

    public static function canBeServeDue($status)
    {
        return in_array($status, [self::PROCESS, self::SERVE_DUE]);
    }

    public static function isScheduleDue($status)
    {
        return $status === self::SCHEDULE_DUE;
    }
}