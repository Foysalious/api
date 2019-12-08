<?php


namespace Sheba\Loan\DS;


class PresentAddress extends Address
{
    public function __construct($info)
    {
        $address=[];
        if (is_array($info))$address=array_key_exists('present_address', $info)?$info['present_address']:[];
        if(is_object($info))$address=isset($info->present_address)?$info->present_address:[];
        parent::__construct($address);
    }

    public function toArray()
    {
        return parent::toArray(); // TODO: Change the autogenerated stub
    }

}