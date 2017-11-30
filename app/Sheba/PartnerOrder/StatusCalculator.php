<?php namespace Sheba\PartnerOrder;

use App\Models\PartnerOrder;

class StatusCalculator
{
    public static $totalJobs;
    public static $jobStatuses;
    public static $statuses;
    public static $jobStatusCounter;

    public static function initialize()
    {
        self::$jobStatuses = constants('JOB_STATUSES');
        self::$statuses = constants('PARTNER_ORDER_STATUSES');
        self::initializeStatusCounter();
    }

    public static function calculate(PartnerOrder $partner_order)
    {
        foreach($partner_order->jobs as $job) {
            self::$jobStatusCounter[$job->status]++;
            self::$totalJobs++;
        }
        return self::get();
    }

    public static function get()
    {
        if (self::isAllJobsCancelled()) {
            return self::$statuses['Cancelled'];
        } else if (self::isAllJobsPending()) {
            return self::$statuses['Open'];
        } else if (self::isAllJobsServed()) {
            return self::$statuses['Closed'];
        } else {
            return self::$statuses['Process'];
        }
    }

    private static function initializeStatusCounter()
    {
        self::$jobStatusCounter = [
            self::$jobStatuses['Pending'] => 0,
            self::$jobStatuses['Accepted'] => 0,
            self::$jobStatuses['Declined'] => 0,
            self::$jobStatuses['Not_Responded'] => 0,
            self::$jobStatuses['Schedule_Due'] => 0,
            self::$jobStatuses['Process'] => 0,
            self::$jobStatuses['Served'] => 0,
            self::$jobStatuses['Cancelled'] => 0
        ];
    }

    /**
     * @return bool
     */
    private static function isAllJobsCancelled()
    {
        return self::$jobStatusCounter[self::$jobStatuses['Cancelled']] == self::$totalJobs;
    }

    /**
     * @return bool
     */
    private static function isAllJobsPending()
    {
        $pending_jobs = self::$jobStatusCounter[self::$jobStatuses['Pending']];
        $cancelled_jobs = self::$jobStatusCounter[self::$jobStatuses['Cancelled']];
        $declined_jobs = self::$jobStatusCounter[self::$jobStatuses['Declined']];
        $not_responded_jobs = self::$jobStatusCounter[self::$jobStatuses['Not_Responded']];
        return $pending_jobs + $cancelled_jobs + $declined_jobs + $not_responded_jobs  == self::$totalJobs;
    }

    /**
     * @return bool
     */
    private static function isAllJobsServed()
    {
        $served_jobs = self::$jobStatusCounter[self::$jobStatuses['Served']];
        $cancelled_jobs = self::$jobStatusCounter[self::$jobStatuses['Cancelled']];
        return $served_jobs + $cancelled_jobs == self::$totalJobs;
    }
}