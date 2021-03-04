<?php namespace Sheba\Business\PayrollComponent;

class Requester
{
    private $name;
    private $setting;
    private $deduction;
    private $addition;

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
        return $this;
    }

    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * @param $addition
     * @return $this
     */
    public function setAddition($addition)
    {
        $this->addition = json_decode($addition, 1);
        return $this;
    }

    public function getAddition()
    {
        return $this->addition;
    }

    public function setDeduction($deduction)
    {
        $this->deduction = json_decode($deduction,1);
        return $this;
    }

    public function getDeduction()
    {
        return $this->deduction;
    }
}
