<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\AttendanceActionLog\ActionChecker\ActionResult;

class AttendanceError
{
    private $errorCode = [];

    public function push($error_code)
    {
        $this->errorCode[] = $error_code;
        return $this;
    }

    public function get()
    {
        if (count($this->errorCode) > 1) return ActionResult::OUT_OF_WIFI_GEO_LOCATION;
        if (empty($this->errorCode)) return null;
        return $this->errorCode[0];
    }
}
