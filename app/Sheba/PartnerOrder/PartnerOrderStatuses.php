<?php namespace Sheba\PartnerOrder;

use Sheba\Helpers\ConstGetter;

class PartnerOrderStatuses
{
    use ConstGetter;

    const OPEN = 'Open';
    const PROCESS = 'Process';
    const CLOSED = 'Closed';
    const CANCELLED = 'Cancelled';

    public static function getClosed()
    {
        return [self::CLOSED, self::CANCELLED];
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

    public static function isClosable($status)
    {
        return in_array($status, self::getClosed());
    }
}