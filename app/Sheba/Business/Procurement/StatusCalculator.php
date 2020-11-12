<?php namespace Sheba\Business\Procurement;

use Carbon\Carbon;
use App\Models\Procurement;
use Sheba\Dal\Procurement\PublicationStatuses;
use Sheba\Helpers\ConstGetter;

class StatusCalculator
{
    use ConstGetter;

    // const IS_DRAFT = 0;

    const IS_PENDING = "pending";
    const IS_ACCEPTED = "accepted";
    const IS_STARTED = "started";
    const IS_SERVED = "served";

    const DRAFT = "Draft";
    const UNPUBLISHED = "Unpublished";
    const PENDING = "Open";
    const ACCEPTED = "Hired";
    const SERVED = "Closed";
    const EXPIRED = "Expired";

    public static function resolveStatus(Procurement $procurement)
    {
        if (self::isProcurementExpired($procurement)) return self::EXPIRED;
        if ($procurement->publication_status == PublicationStatuses::DRAFT) return self::DRAFT;
        if ($procurement->publication_status == PublicationStatuses::UNPUBLISHED) return self::UNPUBLISHED;
        if ($procurement->status == self::IS_PENDING) return self::PENDING;
        if (in_array($procurement->status, [self::IS_ACCEPTED, self::IS_STARTED])) return self::ACCEPTED;
        if ($procurement->status == self::IS_SERVED) return self::SERVED;

        return 'N/A';
    }

    /**
     * @param Procurement $procurement
     * @return bool
     */
    private static function isProcurementExpired(Procurement $procurement)
    {
        return Carbon::now() > $procurement->last_date_of_submission &&
            $procurement->status == self::IS_PENDING &&
            $procurement->publication_status <> PublicationStatuses::UNPUBLISHED;
    }
}
