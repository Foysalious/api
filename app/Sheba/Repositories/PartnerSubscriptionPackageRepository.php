<?php

namespace App\Sheba\Repositories;

use App\Models\PartnerSubscriptionPackage;
use DateTime;

class PartnerSubscriptionPackageRepository
{
    private $package_id;

    /**
     * @param $package_id
     */
    public function __construct($package_id)
    {
        $this->package_id = $package_id;
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return PartnerSubscriptionPackage::find($this->package_id);
    }

    /**
     * @return DateTime|null
     */
    public function getHomepageSettingsUpdatedDate()
    {
        /** @var PartnerSubscriptionPackage $package */
        $package = $this->getPackage();
        return $package->homepage_last_updated_date_time;
    }
}
