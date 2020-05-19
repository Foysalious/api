<?php namespace Sheba\Partner\HomePageSetting\Calculators;

use Sheba\Partner\HomePageSetting\DefaultSetting;
use Sheba\Partner\HomePageSetting\DefaultSettingNew;
use Sheba\Partner\HomePageSetting\Setting;

class Basic extends Setting
{

    protected function setting()
    {

        if(!empty($this->version) && ($this->version >= (int)config('partner.lowest_version_for_emi_in_home_setting')))
            return DefaultSettingNew::get();

        return DefaultSetting::get();
    }
}
