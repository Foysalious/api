<?php

namespace Database\Factories;

use App\Models\PartnerPosService;

class PartnerPosServiceFactory extends Factory
{
    protected $model = PartnerPosService::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'name'                  => 'Food',
            'publication_status'    => 1,
            'is_published_for_shop' => 1,
            'price'                 => 100,
            'wholesale_price'       => 80,
            'stock'                 => 20,
            'unit'                  => 'kg',
            'weight'                => 5,
            'weight_unit'           => 'kg',
        ]);
    }
}
