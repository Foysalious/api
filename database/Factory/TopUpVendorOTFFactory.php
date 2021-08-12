<?php namespace Factory;


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
            'name_en' =>'1GB (0.5GB+0.5GB 4G) | 3 Days',
            'name_bn' => '১ জিবি (০.৫ জিবি+০.৫ জিবি ৪জি) । ৩ দিন',
            'description' =>'Data Pack',
            'type' =>'Internet',
            'sim_type' =>'Prepaid' ,
            'cashback_amount' =>'12.00' ,
            'status' =>'Active',
        ]);
    }
}
