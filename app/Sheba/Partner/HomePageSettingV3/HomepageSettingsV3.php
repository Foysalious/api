<?php

namespace Sheba\Partner\HomePageSettingV3;

use App\Models\Partner;
use App\Sheba\Repositories\PartnerSubscriptionPackageRepository;

class HomepageSettingsV3
{
    private $partner;
    private $packageWiseSettings;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
        $this->packageWiseSettings = new PackageWiseHomepageSettings();
    }

    public function get() : array
    {
        $partner_homepage_settings = $this->partner->home_page_setting_new;
        $home_page_setting = (new PartnerSubscriptionPackageRepository($this->partner->package_id))->getHomepageSettings();
        $home_page_setting = $this->filterIsPublished($home_page_setting);
        if(is_null($partner_homepage_settings)) {
            $this->storeHomepageSettings($home_page_setting);
            return json_decode($home_page_setting);
        }
        $home_page_setting = $this->packageWiseSettings->setPackageSettings($home_page_setting)->setPartnerSettings($partner_homepage_settings)->get();
        $this->addIsNewTag($home_page_setting);
        return $home_page_setting;
    }

    private function storeHomepageSettings($home_page_setting)
    {
        $this->partner->home_page_setting_new = $home_page_setting;
        $this->partner->save();
    }

    /**
     * @param $home_page_setting
     * @return false|string
     */
    public function filterIsPublished($home_page_setting)
    {
        if(is_string($home_page_setting)) $home_page_setting = json_decode($home_page_setting);
        $filtered_settings = array();
        foreach ($home_page_setting as $setting) {
            if($setting->is_published === 1)
                $filtered_settings[] = $setting;
        }

        return json_encode($filtered_settings);
    }

    private function addIsNewTag(&$home_page_setting)
    {
        foreach ($home_page_setting as &$setting) {
            if (is_object($setting)) {
                in_array($setting->key, NewFeatures::get()) ? $setting->is_new = 1 : $setting->is_new = 0;
            } else {
                in_array($setting['key'], NewFeatures::get()) ? $setting['is_new'] = 1 : $setting['is_new'] = 0;
            }
        }
    }
}