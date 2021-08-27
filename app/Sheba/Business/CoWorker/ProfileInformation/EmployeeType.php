<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use Sheba\Helpers\ConstGetter;

class EmployeeType
{
    use ConstGetter;

    const PERMANENT = 'permanent';
    const ON_PROBATION = 'on_probation';
    const CONTRACTUAL = 'contractual';
    const INTERN = 'intern';

    public static function getEmployeeType($employee_type)
    {
        if ($employee_type === self::PERMANENT) return self::PERMANENT;
        if ($employee_type === self::ON_PROBATION) return self::ON_PROBATION;
        if ($employee_type === self::CONTRACTUAL) return self::CONTRACTUAL;
        if ($employee_type === self::INTERN) return self::INTERN;
    }

}
