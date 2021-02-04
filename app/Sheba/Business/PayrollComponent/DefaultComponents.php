<?php namespace Sheba\Business\PayrollComponent;


use Sheba\Helpers\ConstGetter;

class DefaultComponents
{
    use ConstGetter;

    const BASIC_SALARY = 'basic_salary';
    const HOUSE_RENT = 'house_rent';
    const MEDICAL_ALLOWANCE = 'medical_allowance';
    const CONVEYANCE = 'conveyance';

    public static function getComponents($components)
    {
        if ($components === self::BASIC_SALARY) return self::getDefaultComponents()[self::BASIC_SALARY];
        if ($components === self::HOUSE_RENT) return self::getDefaultComponents()[self::HOUSE_RENT];
        if ($components === self::MEDICAL_ALLOWANCE) return self::getDefaultComponents()[self::MEDICAL_ALLOWANCE];
        if ($components === self::CONVEYANCE) return self::getDefaultComponents()[self::CONVEYANCE];
    }

    public static function getDefaultComponents()
    {
        return [
            'basic_salary' => [
                'key' => 'basic_salary',
                'value' => 'Basic Salary',
                'type' => 'gross'
            ],
            'house_rent' => [
                'key' => 'house_rent',
                'value' => 'House Rent',
                'type' => 'gross'
            ],
            'medical_allowance' => [
                'key' => 'medical_allowance',
                'value' => 'Medical Allowance',
                'type' => 'gross'
            ],
            'conveyance' => [
                'key' => 'conveyance',
                'value' => 'Conveyance',
                'type' => 'gross'
            ]
        ];
    }
}