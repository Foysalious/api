<?php

namespace Database\Factories;

use Sheba\Dal\InfoCall\InfoCall;

class InfoCallFactory extends Factory
{
    protected $model = InfoCall::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'customer_mobile' => '01620011019',
            'location_id'     => '4',
            'priority'        => 'High',
            'flag'            => 'Red',
            'status'          => 'Open',
            'service_name'    => 'Ac service',
        ]);
    }
}
