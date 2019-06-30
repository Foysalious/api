<?php namespace Sheba\Business\Purchase;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const PENDING = "Pending";
    const APPROVED = "Approved";
    const NEED_APPROVAL = "Need Approval";
    const REJECTED = "Rejected";

    public static function isApprovable($from)
    {
        return in_array($from, [self::PENDING]);
    }
}