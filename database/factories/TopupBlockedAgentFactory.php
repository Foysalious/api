<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\TopUpBlockedAgent\TopUpBlockedAgent;

class TopupBlockedAgentFactory extends Factory
{
    protected $model = TopUpBlockedAgent::class;

    public function definition(): array
    {
        return array_merge([
            'agent_type' => 'App\Models\Affiliate',
            'reason' => 'recurring_top_up',
            'created_by' => '1',
            'created_by_name' => $this->faker->name,
            'created_at' => Carbon::now(),
        ]);
    }
}