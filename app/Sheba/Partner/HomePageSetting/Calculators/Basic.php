<?php namespace Sheba\Partner\HomePageSetting\Calculators;

use Sheba\Partner\HomePageSetting\DefaultSetting;
use Sheba\Partner\HomePageSetting\Setting;

class Basic extends Setting
{
    public function __construct(Setting $next = null)
    {
        parent::__construct($next);
    }
    protected function setting()
    {
        return DefaultSetting::get();
    }
}
