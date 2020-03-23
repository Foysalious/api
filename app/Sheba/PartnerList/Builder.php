<?php namespace Sheba\PartnerList;


use Illuminate\Support\Collection;
use Sheba\Location\Geo;

interface Builder
{
    public function checkGeoWithinOperationalZone();

    public function checkService();

    public function checkCategory();

    public function checkLeave();

    public function checkPartnerVerification();

    public function checkPartner();

    public function checkCanAccessMarketPlace();

    public function checkGeoWithinPartnerRadius();

    public function checkPartnerHasResource();

    public function checkPartnerCreditLimit();

    public function checkPartnerDailyOrderLimit();

    public function checkPartnerAvailability();

    public function checkOption();

    public function checkPartnersToIgnore();

    public function removeShebaHelpDesk();

    public function removeUnavailablePartners();

    public function resolvePartnerSortingParameters();

    public function sortPartners();

    public function withService();

    public function withSubscriptionPackage();

    public function withResource();

    public function withAvgReview();

    public function withTotalCompletedOrder();

    public function withoutShebaHelpDesk();

    public function runQuery();

    public function setPartnerIds(array $partner_ids);

    public function setPartnerIdsToIgnore(array $partner_ids);

    public function setServiceRequestObjectArray(array $service_request_object);

    public function setGeo(Geo $geo);

    public function setScheduleDate($date);

    public function setScheduleTime($time);

    /** @var  Collection */
    public function get();

    public function first();

}
