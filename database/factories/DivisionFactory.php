<?php
namespace Database\Factories;

use App\Models\Division;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        return [
            'name'    => 'Dhaka',
            'bn_name' => 'ঢাকা',
        ];
    }
}
