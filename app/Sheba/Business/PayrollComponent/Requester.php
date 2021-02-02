<?php namespace Sheba\Business\PayrollComponent;

class Requester
{
    private $name;
    private $setting;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSetting($setting)
    {
        $this->setting = $setting;
        return $setting;
    }

    public function getSetting()
    {
        return $this->setting;
    }
}