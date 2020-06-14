<?php namespace Sheba\Partner\HomePageSetting\Calculators;

use Sheba\Partner\HomePageSetting\DefaultSetting;
use Sheba\Partner\HomePageSetting\Setting;

class Basic extends Setting
{

    protected function setting()
    {
        return DefaultSetting::get();
    }
}
