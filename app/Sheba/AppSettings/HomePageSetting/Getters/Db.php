<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Sheba\AppSettings\HomePageSetting\Settings;

class Db extends Getter
{
    /**
     * @return Settings
     */
    public function getSettings() : Settings
    {
        return new Settings();
    }
}