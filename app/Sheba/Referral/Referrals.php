<?php

namespace Sheba\Referral;

use App\Models\PartnerReferral;

class Referrals
{

    /**
     * @param HasReferrals $referrer
     * @return Referrer
     */
    public static function getReference(HasReferrals $referrer)
    {
        $className = class_basename($referrer);
        /** @var Referrer $class */
        $class = "Sheba\\Referral\\Referrers\\$className";
        return new $class($referrer);
    }

    public static function getReferenceByMobile($mobile)
    {
        return PartnerReferral::where([
            [
                'resource_mobile',
                $mobile
            ],
            [
                'status',
                'pending'
            ]
        ])->first();
    }

    public static function setReference($partner, PartnerReferral $ref)
    {
        if ($ref) {
            $ref->referred_partner_id = $partner->id;
            $ref->status              = 'successful';
            $ref->save();
        }
    }

}
