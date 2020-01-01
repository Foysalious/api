<?php

namespace Sheba\Referral;


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
        $class ="Sheba\\Referral\\Referrers\\$className";
        return new $class($referrer);
    }
}
