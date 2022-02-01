<?php

namespace Database\Factories;

use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;

class AttendanceActionLogFactory extends Factory
{
    protected $model = AttendanceActionLog::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'action' => 'checkin',
            'status' => 'on_time',
        ]);
    }
}
