<?php

namespace Database\Factories;

use Sheba\Dal\ResourceTransaction\Model as ResourceTransaction;

class ResourceTransactionFactory extends Factory
{
    protected $model = ResourceTransaction::class;

    public function definition(): array
    {
        return [
            'job_id'      => '1',
            'resource_id' => '1',
            'type'        => 'Credit',
            'amount'      => '1000',
            'balance'     => '10000',
        ];
    }
}
