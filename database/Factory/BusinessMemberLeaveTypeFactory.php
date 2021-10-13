<?php namespace Factory;

use Sheba\Dal\BusinessMemberLeaveType\Model;

class BusinessMemberLeaveTypeFactory extends Factory
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