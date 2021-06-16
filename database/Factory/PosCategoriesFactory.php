<?php

/**
 * Khairun Nahar
 * pos Catrgory factory for sDelivery Automation
 * May,2021
 */



namespace Factory;


use App\Models\PosCategory;

class PosCategoriesFactory extends Factory
{
    protected function getModelClass()
    {
        return PosCategory::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds,[
            'parent_id' => 1,
            'name' => "test"

        ]);
    }
}