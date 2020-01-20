<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


class ActionError
{
    private $code;
    private $message;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return ActionError
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return ActionError
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

}