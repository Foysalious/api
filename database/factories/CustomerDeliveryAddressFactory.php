<?php

namespace Database\Factories;

use App\Models\CustomerDeliveryAddress;

class CustomerDeliveryAddressFactory extends Factory
{
    protected $model = CustomerDeliveryAddress::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'address'          => 'Road#10, Avenue#9, House#1222&1223 Mirpur DOHS, Dhaka.',
            'mobile'           => '+8801678242955',
            'geo_informations' => '{"lat":23.7391646,"lng":90.3870025}',
        ]);
    }
}
