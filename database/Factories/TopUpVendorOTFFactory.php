<?php namespace Factories;


use Sheba\Dal\TopUpVendorOTF\Model;

class TopUpVendorOTFFactory extends Factory
{

    protected function getModelClass()
    {
       return Model::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'amount' =>'104' ,
            'name_en' =>'jkfhik' ,
            'name_bn' => 'hurefi',
            'description' =>'fgeywgw',
            'type' =>'Bundle',
            'sim_type' =>'Prepaid' ,
            'cashback_amount' =>'12.00' ,
            'status' =>'Active',
        ]);
    }
}
