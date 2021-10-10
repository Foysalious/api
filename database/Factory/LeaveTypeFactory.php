<?php namespace Factory;

use Sheba\Dal\LeaveType\Model;

class LeaveTypeFactory extends Factory
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