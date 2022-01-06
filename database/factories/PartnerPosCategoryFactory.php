<?php

namespace Database\Factories;

use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;

class PartnerPosCategoryFactory extends Factory
{
    protected $model = PartnerPosCategory::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'partner_id'  => 1,
            'category_id' => 1,
        ]);
    }
}
