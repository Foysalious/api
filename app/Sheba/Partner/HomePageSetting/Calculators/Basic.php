<?php namespace Sheba\Partner\HomePageSetting\Calculators;

use Sheba\Partner\HomePageSetting\DefaultSetting;
use Sheba\Partner\HomePageSetting\Setting;

class Basic extends Setting
{

    protected function setting()
    {
        $default_setting = DefaultSetting::get();
        $emi= [
            "key" => "emi",
            "name_en" => "EMI",
            "name_bn" => "কিস্তি",
            "is_on_homepage" => 0
        ];
        if(!empty($this->version) && ($this->version >= (int)config('partner.lowest_version_for_emi_in_home_setting')))
            array_push($default_setting,$emi);

        return $default_setting;
    }
}
