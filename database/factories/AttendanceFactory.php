<?php
namespace Database\Factories;

use Sheba\Dal\Attendance\Model as Attendance;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, []);
    }
}
