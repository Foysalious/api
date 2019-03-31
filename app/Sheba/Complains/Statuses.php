<?php namespace Sheba\Complains;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const OPEN = "Open";
    const OBSERVATION = "Observation";
    const RESOLVED = "Resolved";
    const REJECTED = "Rejected";

    public static function getNotClosed()
    {
        return [self::OPEN, self::OBSERVATION];
    }

    public static function getClosed()
    {
        return [self::REJECTED, self::RESOLVED];
    }

    public static function getClosedString()
    {
        return implode(',', self::getClosed());
    }
}