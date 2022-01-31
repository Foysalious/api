<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;

class AttendanceSummeryFactory extends Factory
{
    protected $model = AttendanceSummary::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'total_present'         => 1,
            'total_late_checkin'    => 1,
            'total_early_checkout'  => 1,
            'total_checkin_grace'   => 1,
            'start_date'            =>Carbon::now(),
        ]);
    }
}