<?php namespace App\Sheba\Business\AttendanceActionLog\ActionChecker\Response;


use Sheba\Business\AttendanceActionLog\ActionChecker\Response\AttendanceActionResponse;

class ActionUnsuccessfulResponse implements AttendanceActionResponse
{
    private $code;
    private $message;

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}