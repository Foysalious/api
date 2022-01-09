<?php

namespace Database\Factories;

use App\Models\PartnerResource;

class PartnerResourceFactory extends Factory
{
    protected $model = PartnerResource::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'is_verified' => 1,
        ]);
    }
}
