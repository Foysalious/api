<?php namespace Factory;

use Sheba\Dal\PayrollComponent\PayrollComponent;

class PayrollComponentFactory extends Factory
{
    protected function getModelClass()
    {
        return PayrollComponent::class;
    }

    protected function getData()
    {
        return [
            'created_by' => '1',
            'created_by_name' => 'Nawshin Tabassum',
            'updated_by' => '1',
            'updated_by_name' => 'Nawshin Tabassum'
        ];
    }
}