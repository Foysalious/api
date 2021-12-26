<?php

namespace Database\Factories;

use App\Models\ResourceSchedule;
use Carbon\Carbon;

class ResourceScheduleFactory extends Factory
{
    protected $model = ResourceSchedule::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'id'          => 1,
            'resource_id' => 1,
            'job_id'      => 1,
            'start'       => Carbon::now(),
            'end'         => Carbon::now(),
            'notify_at'   => Carbon::now(),
        ]);
    }
}
