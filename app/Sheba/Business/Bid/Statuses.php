<?php namespace Sheba\Business\Bid;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const SENT      = "sent";
    const AWARDED   = "awarded";
    const ACCEPTED  = "accepted";
    const REJECTED  = "rejected";

    public static function isAcceptOrRejectable($from)
    {
        return $from == self::AWARDED;
    }
}
