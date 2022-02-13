<?php

namespace Database\Factories;

use Sheba\Dal\CategoryPartner\CategoryPartner;

class CategoryPartnerFactory extends Factory
{
    protected $model = CategoryPartner::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'is_verified' => 1,
        ]);
    }
}
