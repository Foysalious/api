<?php

namespace Database\Factories;

use App\Models\Job;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'job_name'             => 'Pest Control Package Service',
            'service_name'         => 'Pest Control Package Service',
            'service_quantity'     => 4,
            'service_unit_price'   => 200,
            'commission_rate'      => 5,
            'site'                 => 'customer',
            'delivery_charge'      => 0,
            'status'               => 'Pending',
            'schedule_date'        => $this->now->toDateString(),
            'preferred_time'       => $this->now->toTimeString()."-".$this->now->addHour()->toTimeString(),
            'preferred_time_start' => $this->now->toTimeString(),
            'preferred_time_end'   => $this->now->addHour()->toTimeString(),
        ]);
    }
}
