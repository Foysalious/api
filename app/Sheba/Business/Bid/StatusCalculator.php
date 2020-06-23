<?php namespace Sheba\Business\Bid;

use App\Models\Bid;
use Sheba\Helpers\ConstGetter;

class StatusCalculator
{
    use ConstGetter;

    const IS_AWARDED = "awarded";
    const AWARDED = "Awarded";

    const IS_PENDING = "sent";
    const PENDING = "Pending";

    const IS_ACCEPTED = "accepted";
    const ACCEPTED = "Accepted";

    const IS_REJECTED = "rejected";
    const REJECTED = "Rejected";

    public static function resolveStatus(Bid $bid)
    {
        if ($bid->status == self::IS_AWARDED) return self::AWARDED;
        if ($bid->status == self::IS_PENDING) return self::PENDING;
        if ($bid->status == self::IS_ACCEPTED) return self::ACCEPTED;
        if ($bid->status == self::IS_REJECTED) return self::REJECTED;
    }
}