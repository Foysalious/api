<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Sheba\AppSettings\HomePageSetting\DS\Builders\ItemBuilder;
use Sheba\AppSettings\HomePageSetting\DS\Builders\SectionBuilder;
use Sheba\AppSettings\HomePageSetting\DS\Setting;

abstract class Getter
{
    protected $location;
    protected $screen;
    protected $portal;

    /** @var SectionBuilder */
    protected $sectionBuilder;
    /** @var ItemBuilder */
    protected $itemBuilder;

    public function __construct(SectionBuilder $section_builder, ItemBuilder $item_builder)
    {
        $this->sectionBuilder = $section_builder;
        $this->itemBuilder = $item_builder;
    }

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

    /**
     * @return mixed
     */
    public function getPortal()
    {
        return $this->portal;
    }

    /**
     * @return mixed
     */
    public function getScreen()
    {
        return $this->screen;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    abstract public function getSettings(): Setting;
}
