<?php

namespace Database\Factories;

use Sheba\Dal\BusinessOffice\Model as BusinessOffice;

class BusinessOfficeFactory extends Factory
{
    protected $model = BusinessOffice::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'business_id' => 1,
            'name'        => 'Sheba Banani Office',
            'location'    => '{}',
            'ip'          => '103.197.207.12',
        ]);
    }
}
