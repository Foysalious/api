<?php namespace Sheba\Business\Procurement;

use App\Models\Procurement;
use Carbon\Carbon;
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

    const EXPIRED = "Expired";

    public static function resolveStatus(Procurement $procurement)
    {
        if (Carbon::now() > $procurement->last_date_of_submission) return self::EXPIRED;
        if ($procurement->is_published == self::IS_DRAFT) return self::DRAFT;
        if ($procurement->status == self::IS_PENDING) return self::PENDING;
        if ($procurement->status == self::IS_ACCEPTED) return self::ACCEPTED;
        return 'N/A';
    }

}