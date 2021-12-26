<?php

namespace Database\Factories;

use Sheba\Dal\Leave\Model as Leave;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        return [
            'title' => 'Test Leave',
        ];
    }
}
