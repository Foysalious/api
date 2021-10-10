<?php namespace Factory;


use Sheba\Dal\TopUpVendorOTFChangeLog\Model;

class TopUpVendorOTFChangeLogFactory extends Factory
{

    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'from_status' => 'Deactive',
            'to_status'   => 'Active',
            'log'         => 'OTF status changed from Deactive to Active.',
        ]);
    }
}
