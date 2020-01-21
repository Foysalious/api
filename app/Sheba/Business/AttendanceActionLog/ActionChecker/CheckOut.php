<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


use Sheba\Dal\AttendanceActionLog\Actions;

class CheckOut extends ActionChecker
{

    public function getActionName()
    {
        return Actions::CHECKOUT;
    }

    protected function setAlreadyHasActionForTodayResponse()
    {
        $this->setResult(ActionResultCodes::ALREADY_CHECKED_OUT, ActionResultCodeMessages::ALREADY_CHECKED_OUT);
    }
}