<?php namespace Factory;


use Sheba\Dal\JobService\JobService;

class JobServiceFactory extends Factory
{

    protected function getModelClass()
    {
        return JobService::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'unit_price'=>200,
            'min_price'=>5,
        ]);
    }
}