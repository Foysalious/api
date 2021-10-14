<?php namespace Factory;

use Sheba\Dal\CategoryScheduleSlot\CategoryScheduleSlot;

class CategoryScheduleSlotFactory extends Factory
{
    protected function getModelClass()
    {
        return CategoryScheduleSlot::class;
    }

    protected function getData()
    {
        return [
            'category_id' => 2,
            'schedule_slot_id' => 9,
            'day' => 0
        ];
    }
}