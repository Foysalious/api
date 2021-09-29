<?php namespace App\Sheba\Business\PayrollSetting;


class PayrollConstGetter
{
    Const LEAVE_PRORATE_NOTE_FOR_POLICY_ACTION = 'Leave deducted as per payroll policies';
    const BASIC_SALARY = 'basic_salary';
    const HOUSE_RENT = 'house_rent';
    const MEDICAL_ALLOWANCE = 'medical_allowance';
    const CONVEYANCE = 'conveyance';
    const FIXED_AMOUNT = 'fixed_amount';
    const GROSS_SALARY = 'gross';

    const HOUSE_RENT_EXEMPTION = 300000;
    const CONVEYANCE_EXEMPTION = 30000;
    const MEDICAL_ALLOWANCE_EXEMPTION = 120000;
    const MALE_TAX_EXEMPTED =  300000;
    const FEMALE_TAX_EXEMPTED =  350000;
    const SPECIAL_TAX_EXEMPTED =  500000;

    const FIRST_TAX_SLAB = 100000;
    const SECOND_TAX_SLAB = 300000;
    const THIRD_TAX_SLAB = 400000;
    const FOURTH_TAX_SLAB = 500000;

    const FIRST_TAX_SLAB_PERCENTAGE = 5;
    const SECOND_TAX_SLAB_PERCENTAGE = 10;
    const THIRD_TAX_SLAB_PERCENTAGE = 15;
    const FOURTH_TAX_SLAB_PERCENTAGE = 20;
    const FIFTH_TAX_SLAB_PERCENTAGE = 25;

}