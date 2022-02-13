<?php

namespace Database\Factories;

use Sheba\Dal\UniversalSlug\Model as UniversalSlug;

class UniversalSlugsFactory extends Factory
{
    protected $model = UniversalSlug::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'slug' => $this->faker->text,
        ]);
    }
}
