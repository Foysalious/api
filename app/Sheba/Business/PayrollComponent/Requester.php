<?php namespace Sheba\Business\PayrollComponent;

use Sheba\Dal\PayrollComponent\Type;

class Requester
{
    private $name;
    private $setting;
    private $deduction;
    private $addition;
    private $addAdditionComponent;
    private $updateAdditionComponent;
    private $updateDeductionComponent;
    private $addDeductionComponent;
    private $error =  false;
    public $grossComponentAdd;
    public $grossComponentUpdate;

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
        if ($this->addAdditionComponent) $this->hasError($this->addAdditionComponent);
        $this->updateAdditionComponent = $this->addition['update'];
        if ($this->updateAdditionComponent) $this->hasError($this->updateAdditionComponent);
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
        if ($this->addDeductionComponent) $this->hasError($this->addDeductionComponent);
        $this->updateDeductionComponent = $this->deduction['update'];
        if ($this->updateDeductionComponent) $this->hasError($this->updateDeductionComponent);
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

    public function hasError($components)
    {
        $new_components = [];
        foreach ($components as $components_type) {
            foreach ($components_type as $key => $components_value) {
                if ($key == 'name') array_push($new_components, $components_value);
            }
        }
        $existing_components = $this->setting->components->whereIn('type', [Type::ADDITION, Type::DEDUCTION])->pluck('name')->toArray();
        if (count(array_intersect($new_components, $existing_components)) > 0) $this->error = true;

        return $this->error;
    }

    public function checkError()
    {
        return $this->error;
    }

    public function setGrossComponentAdd($gross_component_add)
    {
        $this->grossComponentAdd = json_decode($gross_component_add, 1);
        return $this;
    }

    public function setGrossComponentUpdate($gross_component_update)
    {
        $this->grossComponentUpdate = json_decode($gross_component_update, 1);
        return $this;
    }
}
