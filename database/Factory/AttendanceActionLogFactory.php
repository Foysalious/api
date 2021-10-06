<?php namespace Factory;

use Sheba\Dal\AttendanceActionLog\Model;

class AttendanceActionLogFactory extends Factory
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