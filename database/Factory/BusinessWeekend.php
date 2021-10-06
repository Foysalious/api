<?php namespace Factory;

use Sheba\Dal\BusinessWeekend\Model;

class BusinessWeekend extends Factory
{
    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return [];
    }
}