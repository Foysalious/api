<?php namespace Sheba\Business\Leave\SuperAdmin;

use Sheba\Helpers\ConstGetter;

class LeaveEditType
{
    use ConstGetter;

    const STATUS = 'status';
    const LEAVE_TYPE = 'leave_type';
    const LEAVE_DATE = 'leave_date';
    const SUBSTITUTE = 'substitute';
    const LEAVE_ADJUSTMENT = 'leave_adjustment';
    const LEAVE_UPDATE = 'leave_update';
}
