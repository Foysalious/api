<?php namespace Factory;

use App\Models\BusinessMember;
use Sheba\Dal\Attendance\Model;

class AttendanceFactory extends Factory
{
    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, []);
    }
}