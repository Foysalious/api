<?php namespace Sheba\Partner\HomePageSettingV3\Calculators;

use Sheba\Partner\HomePageSettingV3\DefaultSetting;
use Sheba\Partner\HomePageSettingV3\SettingV3;

class Basic extends SettingV3
{
    public function __construct(SettingV3 $next = null)
    {
        parent::__construct($next);
    }
    protected function setting()
    {
        return DefaultSetting::get();
    }
}
