<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

use Sheba\Dal\AttendanceActionLog\Actions;

class ActionProcessor
{
    /** @var Action */
    private $action;

    public function setActionName($action)
    {
        $this->action = $this->getMethod($action);
        return $this;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }


    /**
     * @param $action
     * @return bool
     */
    private function isValidMethod($action)
    {
        return in_array($action, Actions::get());
    }


    private function getMethod($action)
    {
        if (!$this->isValidMethod($action)) throw new \InvalidArgumentException('Invalid Method.');

        switch ($action) {
            case Actions::CHECKIN:
                return new CheckIn();
            case Actions::CHECKOUT:
                return new Checkout();
        }
    }
}