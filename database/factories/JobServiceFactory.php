<?php

namespace Database\Factories;

use Sheba\Dal\JobService\JobService;

class JobServiceFactory extends Factory
{
    protected $model = JobService::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'unit_price' => 200,
            'min_price'  => 5,
        ]);
    }
}
