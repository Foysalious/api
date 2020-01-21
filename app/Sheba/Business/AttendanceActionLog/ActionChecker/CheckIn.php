<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


use Sheba\Dal\AttendanceActionLog\Actions;

class CheckIn extends ActionChecker
{

    public function getActionName()
    {
        return Actions::CHECKIN;
    }

    protected function setAlreadyHasActionForTodayResponse()
    {
        $this->setResult(ActionResultCodes::ALREADY_CHECKED_IN, ActionResultCodeMessages::ALREADY_CHECKED_IN);
    }
}