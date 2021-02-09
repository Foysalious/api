<?php namespace Factory;

use App\Models\ScheduleSlot;
class ScheduleSlotsFactory extends Factory
{

    protected function getModelClass()
    {
        return ScheduleSlotsFactory::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'id' => 1,
            'start' => '00:00:00',
            'end' => '01:00:00',
            'created_by' => 6,
            'created_by_name' => 'IT - Hasan Hafiz Pasha',
            'updated_by' => 6,
            'updated_by_name' => 'IT - Hasan Hafiz Pasha'
        ]);// TODO: Implement getData() method.
    }
}