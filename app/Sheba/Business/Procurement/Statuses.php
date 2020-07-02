<?php namespace Sheba\Business\Procurement;

class Statuses
{
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const NEED_APPROVAL = 'need_approval';
    const STARTED = 'started';
    const SERVED = 'served';
    const CANCELLED = 'cancelled';

    public static function getOpen()
    {
        return [self::PENDING];
    }
}
