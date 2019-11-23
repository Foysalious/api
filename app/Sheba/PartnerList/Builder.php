<?php namespace App\Sheba\PartnerList;


use Sheba\Location\Geo;

interface Builder
{
    public function checkGeoWithinOperationalZone();

    public function checkService();

    public function checkCategory();

    public function checkLeave();

    public function checkVerification();

    public function checkPartner();

    public function checkCanAccessMarketPlace();

    public function checkGeoWithinPartnerRadius();

    public function checkPartnerHasResource();

    public function checkPartnerCreditLimit();

    public function checkDailyOrderLimit();

    public function checkAvailability();

    public function checkOption();

    public function removeShebaHelpDesk();

    public function removeUnavailablePartners();

    public function withService();

    public function withResource();

    public function WithAvgReview();

    public function runQuery();

    public function setPartnerIds(array $partner_ids);

    public function setServiceRequestObjectArray(array $service_request_object);

    public function setGeo(Geo $geo);

    public function setScheduleDate($date);

    public function setScheduleTime($time);

}