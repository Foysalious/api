<?php namespace Factory;


use App\Models\PartnerResource;

class PartnerResourceFactory extends Factory
{

    protected function getModelClass()
    {
        return PartnerResource::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'is_verified'=> 1
        ]);
    }
}
