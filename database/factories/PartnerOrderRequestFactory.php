<?php

namespace Database\Factories;

use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;

class PartnerOrderRequestFactory extends Factory
{
    protected $model = PartnerOrderRequest::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'status' => 'pending',
        ]);
    }
}
