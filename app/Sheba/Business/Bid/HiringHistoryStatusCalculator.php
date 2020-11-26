<?php namespace Sheba\Business\Bid;

use App\Models\Bid;
use Sheba\Helpers\ConstGetter;

class HiringHistoryStatusCalculator
{
    use ConstGetter;

    const IS_AWARDED = "awarded";
    const AWARDED = "Pending";

    const IS_ACCEPTED = "accepted";
    const ACCEPTED = "Accepted";

    const IS_REJECTED = "rejected";
    const REJECTED = "Rejected";

    public static function resolveStatus(Bid $bid)
    {
        if ($bid->status == self::IS_AWARDED) return self::AWARDED;
        if ($bid->status == self::IS_ACCEPTED) return self::ACCEPTED;
        if ($bid->status == self::IS_REJECTED) return self::REJECTED;
    }
}