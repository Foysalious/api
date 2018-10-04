<?php namespace App\Sheba\Partner;

class PartnerStatuses
{
    const VERIFIED = "Verified";
    const UNVERIFIED = "Unverified";
    const PAUSED = "Paused";
    const CLOSED = "Closed";
    const BLACKLISTED = "Blacklisted";
    const WAITING = "Waiting";
    const ONBOARDED = "Onboarded";
    const REJECTED = "Rejected";

    /**
     * @param $status
     * @return array
     */
    public static function getChangeableStatuses($status)
    {
        switch ($status) {
            case self::VERIFIED:
                return [self::UNVERIFIED, self::PAUSED, self::BLACKLISTED, self::CLOSED];
                break;
            case self::UNVERIFIED:
                return [self::VERIFIED, self::PAUSED, self::BLACKLISTED];
                break;
            case self::WAITING:
                return [self::VERIFIED, self::REJECTED];
                break;
            case self::BLACKLISTED:
                return [self::VERIFIED, self::PAUSED];
                break;
            case self::ONBOARDED:
                return [self::VERIFIED, self::UNVERIFIED, self::BLACKLISTED];
                break;
            default:
                return [self::VERIFIED, self::UNVERIFIED, self::BLACKLISTED];
        }
    }
}