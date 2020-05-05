<?php namespace App\Sheba\Subscription\Partner;

class PartnerSubscriptionChange
{
    const UPGRADE = 'Upgrade';
    const DOWNGRADE = 'Downgrade';
    const RENEWED = 'Renewed';

    public static function all()
    {
        return [self::UPGRADE => 'upgrade', self::DOWNGRADE => 'downgrade', self::RENEWED => 'renewed'];
    }
}
