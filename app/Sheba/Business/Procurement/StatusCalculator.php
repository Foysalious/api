<?php namespace Sheba\Business\Procurement;

use Carbon\Carbon;
use App\Models\Procurement;
use Sheba\Helpers\ConstGetter;

class StatusCalculator
{
    use ConstGetter;

    const IS_DRAFT = 0;
    const DRAFT = "Draft";

    const IS_PENDING = "pending";
    const PENDING = "Open";

    const IS_ACCEPTED = "accepted";
    const ACCEPTED = "Hired";

    const IS_SERVED = "served";
    const SERVED = "Closed";

    const EXPIRED = "Expired";

    public static function resolveStatus(Procurement $procurement)
    {
        if (Carbon::now() > $procurement->last_date_of_submission && $procurement->status == self::IS_PENDING) return self::EXPIRED;
        if ($procurement->is_published == self::IS_DRAFT) return self::DRAFT;
        if ($procurement->status == self::IS_PENDING) return self::PENDING;
        if ($procurement->status == self::IS_ACCEPTED) return self::ACCEPTED;
        if ($procurement->status == self::IS_SERVED) return self::SERVED;
        return 'N/A';
    }

}