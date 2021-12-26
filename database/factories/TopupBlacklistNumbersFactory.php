<?php

namespace Database\Factories;

use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;

class TopupBlacklistNumbersFactory extends Factory
{
    protected $model = TopUpBlacklistNumber::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'name'   => 'Test',
            'mobile' => '+8801678987656',
        ]);
    }
}
