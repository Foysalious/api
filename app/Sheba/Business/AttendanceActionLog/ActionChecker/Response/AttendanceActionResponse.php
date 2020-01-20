<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker\Response;


interface AttendanceActionResponse
{
    public function getCode();
    public function setCode($code);
    public function getMessage();
    public function setMessage($message);
}