<?php

namespace Database\Factories;

use Sheba\Dal\CategoryLocation\CategoryLocation;

class CategoryLocationFactory extends Factory
{
    protected $model = CategoryLocation::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'is_logistic_enabled' => 1,
        ]);
    }
}
