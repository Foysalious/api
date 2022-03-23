<?php

namespace App\Sheba\DynamicForm;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use Sheba\Helpers\BasicGetter;

class PartnerMefInformation implements Arrayable
{
    use BasicGetter;

    private $fatherName;
    private $motherName;
    private $nomineeDOB;
    private $nomineeName;
    private $nomineePhone;
    private $presentAddress;
    private $businessStartDt;
    private $nomineeRelation;
    private $presentDistrict;
    private $presentDivision;
    private $presentPostCode;
    private $permanentAddress;
    private $nomineeFatherName;
    private $nomineeMotherName;
    private $tradeLicenseExists;
    private $permanentPostCode;

    public function setProperty($input): PartnerMefInformation
    {
        foreach ($input as $key => $value)
            if (isset($this->$key)) $this->$key = $value;

        return $this;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    public function toArray(): array
    {
        $reflection_class = new ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            if(isset($this->{$item->name}))
                $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

    public function getAvailable(): array
    {
        $reflection_class = new ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item)
            $data[$item->name] = $this->{$item->name};

        return $data;
    }
}