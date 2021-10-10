<?php namespace Factory;

use Sheba\Dal\PayrollSetting\PayrollSetting;

class PayrollSettingFactory extends Factory
{
    protected function getModelClass()
    {
        return PayrollSetting::class;
    }

    protected function getData()
    {
        return [
            'is_enable' => '1',
            'created_by' => '1',
            'created_by_name' => 'Nawshin Tabassum',
            'updated_by' => '1',
            'updated_by_name' => 'Nawshin Tabassum'
        ];
    }
}