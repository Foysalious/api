<?php


namespace Factory;

use App\Models\Bonus;

class PartnerBonus extends Factory
{
    protected function getModelClass()
    {
        return Bonus::class; // TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [


        ]);// TODO: Implement getData() method.
    }

}