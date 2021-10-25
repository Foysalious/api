<?php

namespace Sheba\Partner\HomePageSettingV3;

class PackageWiseHomepageSettings
{
    private $package_settings;
    private $partner_settings;

    /**
     * @param mixed $package_settings
     * @return PackageWiseHomepageSettings
     */
    public function setPackageSettings($package_settings): PackageWiseHomepageSettings
    {
        $this->package_settings = json_decode($package_settings);
        return $this;
    }

    /**
     * @param mixed $partner_settings
     * @return PackageWiseHomepageSettings
     */
    public function setPartnerSettings($partner_settings): PackageWiseHomepageSettings
    {
        $this->partner_settings = json_decode($partner_settings);
        return $this;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        foreach ($this->package_settings as $key => &$setting) {
            if($setting->is_published === 1)
                foreach ($this->partner_settings as $partner_setting)
                    if ($setting->key === $partner_setting->key)
                        $setting->is_on_homepage = $partner_setting->is_on_homepage;
        }

        return $this->package_settings;
    }
}