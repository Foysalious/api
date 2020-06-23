<?php namespace Sheba\Business\Procurement;

use App\Models\Procurement;
use Sheba\Helpers\ConstGetter;

class OrderStatusCalculator
{
    use ConstGetter;

    const IS_ACCEPTED = "accepted";
    const ACCEPTED = "Accepted";

    const IS_STARTED = "started";
    const STARTED = "Process";

    const IS_SERVED = "served";
    const SERVED = "Served";

    const IS_CANCELLED = "cancelled";
    const CANCELLED = "Cancelled";

    public static function resolveStatus(Procurement $procurement)
    {
        if ($procurement->status == self::IS_ACCEPTED) return self::ACCEPTED;
        if ($procurement->status == self::IS_STARTED) return self::STARTED;
        if ($procurement->status == self::IS_SERVED) return self::SERVED;
        if ($procurement->status == self::IS_CANCELLED) return self::CANCELLED;
    }
}