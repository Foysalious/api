<?php

namespace Sheba\Partner\HomePageSettingV3;

use App\Models\Partner;
use App\Sheba\Repositories\PartnerSubscriptionPackageRepository;

class HomepageSettingsV3
{
    private $partner;
    private $packageWiseSettings;

    /**
     * @param Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
        $this->packageWiseSettings = new PackageWiseHomepageSettings();
    }

    public function get() : array
    {
        $partner_homepage_settings = $this->partner->home_page_setting_new;
        $home_page_setting = $this->getFilteredPackageSettings();
        if(is_null($partner_homepage_settings)) {
            $this->storeHomepageSettings($home_page_setting);
            return json_decode($home_page_setting);
        }
        $home_page_setting = $this->packageWiseSettings->setPackageSettings($home_page_setting)->setPartnerSettings($partner_homepage_settings)->get();
        $this->addIsNewTag($home_page_setting);
        return $home_page_setting;
    }

    private function getFilteredPackageSettings()
    {
        $home_page_setting = (new PartnerSubscriptionPackageRepository($this->partner->package_id))->getHomepageSettings();
        return $this->filterIsPublished($home_page_setting);
    }

    /**
     * @param $home_page_setting
     */
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

    /**
     * @param $home_page_setting
     */
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

    /**
     * @param $request_settings
     * @param $partner_repo
     * @return mixed|void
     */
    public function update($request_settings, $partner_repo)
    {
        $package_settings = $this->getFilteredPackageSettings();
        if(empty($this->partner->home_page_setting_new)) {
            $this->storeHomepageSettings($request_settings);
            return $request_settings;
        }
        $home_page_setting = $this->packageWiseSettings->setPackageSettings($package_settings)->setPartnerSettings($request_settings)->get();
        $data['home_page_setting_new'] = json_encode($home_page_setting);
        $partner_repo->update($this->partner, $data);
        return json_encode($home_page_setting);
    }
}