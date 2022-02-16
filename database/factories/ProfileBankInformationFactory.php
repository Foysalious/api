<?php

namespace Database\Factories;

use App\Models\ProfileBankInformation;

class ProfileBankInformationFactory extends Factory
{
    protected $model = ProfileBankInformation::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'bank_name' => 'city_bank',
            'account_no' => '12345678910',
        ]);
    }
}