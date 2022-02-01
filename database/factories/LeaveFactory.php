<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\Leave\Model as Leave;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        return [
            'title'                  => 'Test Leave',
            'start_date'             => Carbon::now(),
            'end_date'               => Carbon::now()->addDay()->timestamp,
            'is_half_day'            => 0,
            'note'                   => 'Test leave',
            'half_day_configuration' => '',
        ];
    }
}
