<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Sheba\AppSettings\HomePageSetting\DS\Setting;

abstract class Getter
{
    protected $location;
    protected $screen;
    protected $portal;

    /**
     * @param mixed $location
     * @return Getter
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @param mixed $screen
     * @return Getter
     */
    public function setScreen($screen)
    {
        $this->screen = $screen;
        return $this;
    }

    /**
     * @param mixed $portal
     * @return Getter
     */
    public function setPortal($portal)
    {
        $this->portal = $portal;
        return $this;
    }

    abstract public function getSettings() : Setting;
}