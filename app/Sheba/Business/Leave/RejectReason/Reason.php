<?php namespace Sheba\Business\Leave\RejectReason;

use Sheba\Helpers\ConstGetter;

class Reason
{
    use ConstGetter;

    const BREACH_OF_LEAVE_POLICY = 'breach_of_leave_policy';
    const REASONABLE_NOTICE_OBLIGATION = 'reasonable_notice_obligation';
    const NOT_A_VALID_LEAVE_REQUEST = 'not_a_valid_leave_request';
    const VERY_FREQUENT_LEAVE_REQUESTS = 'very_frequent_leave_requests';
    const OTHER = 'other';

    public static function getComponents($reason)
    {
        if ($reason === self::BREACH_OF_LEAVE_POLICY) return self::getReasons()[self::BREACH_OF_LEAVE_POLICY];
        if ($reason === self::REASONABLE_NOTICE_OBLIGATION) return self::getReasons()[self::REASONABLE_NOTICE_OBLIGATION];
        if ($reason === self::NOT_A_VALID_LEAVE_REQUEST) return self::getReasons()[self::NOT_A_VALID_LEAVE_REQUEST];
        if ($reason === self::VERY_FREQUENT_LEAVE_REQUESTS) return self::getReasons()[self::VERY_FREQUENT_LEAVE_REQUESTS];
        if ($reason === self::OTHER) return self::getReasons()[self::OTHER];
    }

    public static function getReasons()
    {
        return [
            'breach_of_leave_policy' => 'Breach of leave policy',
            'reasonable_notice_obligation' => 'Reasonable notice obligation',
            'not_a_valid_leave_request' => 'Not a valid leave request',
            'very_frequent_leave_requests' => 'Very frequent leave requests',
            'other' => 'Other',
        ];
    }

    public static function getReasonsV2()
    {
        return [
            'breach_of_leave_policy' => ['breach_of_leave_policy' => 'Breach of leave policy'],
            'reasonable_notice_obligation' => ['reasonable_notice_obligation' => 'Reasonable notice obligation'],
            'not_a_valid_leave_request' => ['not_a_valid_leave_request' => 'Not a valid leave request'],
            'very_frequent_leave_requests' => ['very_frequent_leave_requests' => 'Very frequent leave requests'],
            'other' => ['other' => 'Other'],
        ];
    }
}
