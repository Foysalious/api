<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;

class AttendanceOverrideFactory extends Factory
{
    protected $model = AttendanceOverrideLogs::class;
    
    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'action'          => 'checkin',
            'previous_time'   => '15:28:41',
            'new_time'        => '13:28:41',
            'previous_status' => 'on_time',
            'new_status'      => 'on_time',
            'log'             => 'test',
        ]);
    }
}
