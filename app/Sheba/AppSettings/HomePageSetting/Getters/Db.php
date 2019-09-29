<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Sheba\AppSettings\HomePageSetting\DS\Setting;

class Db extends Getter
{
    /**
     * @return Setting
     */
    public function getSettings() : Setting
    {
        return new Setting();
    }
}