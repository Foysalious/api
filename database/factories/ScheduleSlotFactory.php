<?php

namespace Database\Factories;

use App\Models\ScheduleSlot;

class ScheduleSlotFactory extends Factory
{
    protected $model = ScheduleSlot::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'start' => '00:00:00',
            'end'   => '01:00:00',
        ]);
    }
}
