<?php namespace Factory;


use App\Models\PartnerResource;

class PartnerResourceFactory extends Factory
{

    protected function getModelClass()
    {
        return PartnerResource::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'is_verified'=> 1
        ]);// TODO: Implement getData() method.
    }
}
