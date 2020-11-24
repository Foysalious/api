<?php namespace Sheba\Partner\HomePageSettingV3\Calculators;

use Sheba\Partner\HomePageSettingV3\DefaultSettingV3;
use Sheba\Partner\HomePageSettingV3\SettingV3;

class Basic extends SettingV3
{

    protected function setting()
    {
        return DefaultSettingV3::get();
    }
}
