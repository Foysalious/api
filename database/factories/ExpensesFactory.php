<?php

namespace Database\Factories;

use Sheba\Dal\Expense\Expense;

class ExpensesFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'amount'                    => '100',
            'remarks'                   => 'Test Expense',
            'type'                      => 'other',
            'status'                    => 'pending',
            'is_updated_by_super_admin' => 0,
        ]);
    }
}
