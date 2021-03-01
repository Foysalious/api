<?php namespace Factory;



use Sheba\Dal\CategoryPartner\CategoryPartner;

class CategoryPartnerFactory extends Factory
{

    protected function getModelClass()
    {
        return CategoryPartner::class;
    }

    protected function getData(){
        return array_merge($this->commonSeeds, [
            'is_verified'=>1
        ]);

    }
}
