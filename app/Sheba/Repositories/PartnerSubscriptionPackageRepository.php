<?php

namespace App\Sheba\Repositories;

use App\Models\PartnerSubscriptionPackage;
use DateTime;

class PartnerSubscriptionPackageRepository
{
    private $package_id;
    /**
     * @var PartnerSubscriptionPackage
     */
    private $package;

    /**
     * @param $package_id
     */
    public function __construct($package_id)
    {
        $this->package_id = $package_id;
        $this->package = PartnerSubscriptionPackage::find($this->package_id);
    }

    /**
     * @return DateTime|null
     */
    public function getHomepageSettingsUpdatedDate($billing_start_date)
    {
        return max($this->package->homepage_last_updated_date_time, $billing_start_date);
    }

    public function getHomepageSettings()
    {
        return $this->package->homepage_settings;
    }
}
