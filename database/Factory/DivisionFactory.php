<?php namespace Factory;

use App\Models\Division;

class DivisionFactory extends Factory
{
    protected function getModelClass()
    {
        return Division::class;
    }

    protected function getData()
    {
        return [
            'name' => 'Dhaka',
            'bn_name' => 'ঢাকা'
        ];
    }
}