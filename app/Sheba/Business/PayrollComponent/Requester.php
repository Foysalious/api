<?php namespace Sheba\Business\PayrollComponent;

class Requester
{
    private $name;
    private $setting;
    private $deduction;
    private $addition;
    private $addAdditionComponent;
    private $updateAdditionComponent;
    /**
     * @var mixed
     */
    private $updateDeductionComponent;
    /**
     * @var mixed
     */
    private $addDeductionComponent;

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
        $this->addAdditionComponent = $this->addition['add'];
        $this->updateAdditionComponent = $this->addition['update'];
        return $this;
    }

    public function getAddAdditionComponent()
    {
        return $this->addAdditionComponent;
    }

    public function getUpdateAdditionComponent()
    {
        return $this->updateAdditionComponent;
    }

    public function setDeduction($deduction)
    {
        $this->deduction = json_decode($deduction,1);
        $this->addDeductionComponent = $this->deduction['add'];
        $this->updateDeductionComponent = $this->deduction['update'];
        return $this;
    }

    public function getAddDeductionComponent()
    {
        return $this->addDeductionComponent;
    }

    public function getUpdateDeductionComponent()
    {
        return $this->updateDeductionComponent;
    }
}
