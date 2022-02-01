<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\Support\Model as Support;

class SupportFactory extends Factory
{
    protected $model = Support::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'member_id'        => 1,
            'long_description' => 'Test Ticket',
            'closed_at'        => Carbon::now(),
        ]);
    }
}
