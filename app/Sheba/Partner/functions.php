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



if (!function_exists('resolvePartnerFromAuthMiddleware')) {
    /**
     * @param $request
     * @return Partner
     */
    function resolvePartnerFromAuthMiddleware($request)
    {
        $partner = $request->partner;
        if ($partner instanceof Partner)
            return $partner;
        return $request->auth_user->getPartner();
    }
}

if (!function_exists('resolveManagerResourceFromAuthMiddleware')) {
    /**
     * @param $request
     * @return Partner
     */
    function resolveManagerResourceFromAuthMiddleware($request)
    {
        $partner = $request->partner;
        if ($partner instanceof Partner)
            return $partner->getFirstAdminResource();
        return $request->auth_user->getPartner()->getFirstAdminResource();
    }
}
