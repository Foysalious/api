<?php

namespace Database\Factories;

use App\Models\PosCategory;

class PosCategoryFactory extends Factory
{
    protected $model = PosCategory::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'parent_id' => 1,
            'name'      => "test",
        ]);
    }
}
