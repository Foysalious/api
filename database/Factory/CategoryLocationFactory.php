<?php namespace Factory;


use Sheba\Dal\CategoryLocation\CategoryLocation;

class CategoryLocationFactory extends Factory

{

    protected function getModelClass()
    {
        return CategoryLocation::class;

    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'is_logistic_enabled'=>1,

        ]);

    }
}