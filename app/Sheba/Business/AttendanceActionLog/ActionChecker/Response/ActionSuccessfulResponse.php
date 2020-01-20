<?php namespace App\Sheba\Business\AttendanceActionLog\ActionChecker\Response;


use Sheba\Business\AttendanceActionLog\ActionChecker\Response\AttendanceActionResponse;

class ActionSuccessfulResponse implements AttendanceActionResponse
{
    private $code;
    private $message;

    public function getCode()
    {
        // TODO: Implement getCode() method.
    }

    public function getMessage()
    {
        // TODO: Implement getMessage() method.
    }

    public function setCode($code)
    {
        $this->code=$code;
        return $this;
    }

    public function setMessage($message)
    {
        $this->message=$message;
        return $this;
    }
}