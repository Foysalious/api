<?php

namespace Database\Factories;

use App\Models\Bonus;

class PartnerBonus extends Factory
{
    protected $model = Bonus::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, []);
    }
}
