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
    private $grossComponentAdd;
    private $grossComponentUpdate;
    private $grossComponentDelete;
    private $componentDeleteData;
    private $additionDelete;
    private $deductionDelete;

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

    public function setComponentDelete($component_delete_data)
    {
        $this->componentDeleteData = json_decode($component_delete_data,1);
        $this->additionDelete = $this->componentDeleteData['addition'];
        $this->deductionDelete = $this->componentDeleteData['deduction'];
        return $this;
    }

    public function getAdditionComponentDelete()
    {
        return $this->additionDelete;
    }

    public function getDeductionComponentDelete()
    {
        return $this->deductionDelete;
    }

    public function hasError($components)
    {
        foreach ($components as $components_type) {
            foreach ($components_type as $key => $components_value) {
                if ($key == 'name') {
                    $existing_component = $this->setting->components->where('name', $components_value)->whereIn('type', [Type::ADDITION, Type::DEDUCTION])->first();
                    if ($existing_component) return $this->error = true;
                }
            }
        }
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
    public function getGrossComponentAdd()
    {
        return $this->grossComponentAdd;
    }

    public function setGrossComponentUpdate($gross_component_update)
    {
        $this->grossComponentUpdate = json_decode($gross_component_update, 1);
        return $this;
    }

    public function getGrossComponentUpdate()
    {
        return $this->grossComponentUpdate;
    }

    public function setGrossComponentDelete($gross_component_delete)
    {
        $this->grossComponentDelete = json_decode($gross_component_delete, 1);
        return $this;
    }

    public function getGrossComponentDelete()
    {
        return $this->grossComponentDelete;
    }
}
