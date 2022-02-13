<?php
namespace Database\Factories;

use Sheba\Dal\CategoryScheduleSlot\CategoryScheduleSlot;

class CategoryScheduleSlotFactory extends Factory
{
    protected $model = CategoryScheduleSlot::class;

    public function definition(): array
    {
        return [
            'category_id'      => 2,
            'schedule_slot_id' => 9,
            'day'              => 0,
        ];
    }
}
