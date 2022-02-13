<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\TopUpBlockedAgentLog\TopUpBlockedAgentLog;

class TopupBlockedAgentLogFactory extends Factory
{
    protected $model = TopUpBlockedAgentLog::class;

    public function definition(): array
    {
        return array_merge([
            'action' => 'block',
            'reason' => 'recurring_top_up',
            'created_by' => '1',
            'created_by_name' => $this->faker->name,
            'created_at' => Carbon::now(),
        ]);
    }
}