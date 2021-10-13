<?php namespace Factory;

use Sheba\Dal\Leave\Model;

class LeaveFactory extends Factory
{
    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return [
            'title' => 'Test Leave'
        ];
    }
}