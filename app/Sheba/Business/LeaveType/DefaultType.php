<?php namespace Sheba\Business\LeaveType;

use Sheba\Helpers\ConstGetter;

class DefaultType
{
    use ConstGetter;

    const ANNUAL = 'Annual Leave';
    const SICK = 'Sick Leave';

    public static function getDays()
    {
        return ['ANNUAL' => 21, 'SICK' => 14];
    }
}
