<?php namespace Factory;

use Sheba\Dal\ResourceTransaction\Model;

class ModelFactory extends Factory
{
    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return [
            'job_id' => '1',
            'resource_id' => '1',
            'type' => 'Credit',
            'amount' => '1000',
            'balance' => '10000',
        ];
    }
}