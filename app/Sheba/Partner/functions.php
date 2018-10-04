<?php

use App\Models\Partner;
use Sheba\Partner\WaitingStatusProcessor;

if (!function_exists('isPartnerReadyToVerified')) {
    /**
     * @param $partner
     * @return bool
     */
    function isPartnerReadyToVerified($partner)
    {
        if (!($partner instanceof Partner)) {
            $partner = Partner::find($partner);
        }

        return (new WaitingStatusProcessor())->setPartner($partner)->isEligibleForWaiting();
    }
}