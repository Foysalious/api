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
            'start' => '00:00:00',
            'end' => '01:00:00',
        ]);// TODO: Implement getData() method.
    }
}