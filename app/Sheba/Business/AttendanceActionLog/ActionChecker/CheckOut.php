<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


use Sheba\Dal\AttendanceActionLog\Actions;

class CheckOut extends Action
{
    public function canTakeTheAction()
    {
        if (!$this->attendanceOfToday) return 1;
        if (!$this->checkAlreadyHasActionForToday()) return 1;
    }


    public function getActionName()
    {
        return Actions::CHECKOUT;
    }

    protected function checkAlreadyHasActionForToday()
    {
        if ($this->getAttendanceActionLog()) {
            $this->actionError->setCode(ActionErrorCodes::ALREADY_CHECKED_OUT)->setMessage(ActionErrorCodeMessages::ALREADY_CHECKED_OUT);
            return 0;
        }
        return 1;
    }
}