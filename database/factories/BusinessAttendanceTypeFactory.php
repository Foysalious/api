<?php

namespace Database\Factories;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Dal\BusinessAttendanceTypes\Model as BusinessAttendanceType;

class BusinessAttendanceTypeFactory extends Factory
{
    protected $model = BusinessAttendanceType::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'attendance_type'    => 'ip_based',
        ]);
    }
}