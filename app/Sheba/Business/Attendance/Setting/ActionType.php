<?php namespace Sheba\Business\Attendance\Setting;

use Sheba\Helpers\ConstGetter;

class ActionType
{
    use ConstGetter;

    const ADD = 'add';
    const EDIT = 'edit';
    const DELETE = 'delete';
}
