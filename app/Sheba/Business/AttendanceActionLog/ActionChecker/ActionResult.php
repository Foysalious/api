<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

class ActionResult
{
    const LATE_TODAY = 501;
    const OUT_OF_WIFI_AREA = 502;
    const DEVICE_UNAUTHORIZED = 503;
    const ALREADY_CHECKED_IN = 504;
    const ALREADY_CHECKED_OUT = 505;
    const CHECKIN_FIRST = 506;
    const ALREADY_DEVICE_USED = 507;
    const LEFT_EARLY_TODAY = 508;
    const OUT_OF_GEO_LOCATION = 509;
    const OUT_OF_WIFI_GEO_LOCATION = 510;
    const SUCCESSFUL = 200;

    const SUCCESSFUL_CHECKOUT_MESSAGE = "Good Bye! See you next day.";
    const SUCCESSFUL_CHECKIN_MESSAGE = "You have successfully checked-in";

    private static $MESSAGES = [
        self::LATE_TODAY => "Oops! You’re late today!",
        self::LEFT_EARLY_TODAY => "Good Bye! You're early check-out today!",
        self::OUT_OF_WIFI_AREA => "You are not inside our wi-fi coverage area!",
        self::DEVICE_UNAUTHORIZED => "You can not check-out from this phone. Please use the same phone you checked-in with",
        self::ALREADY_CHECKED_IN => "You've already checked in",
        self::ALREADY_CHECKED_OUT => "You've already checked out",
        // self::SUCCESSFUL_CHECKOUT => "Good Bye! See you next day.",
        // self::SUCCESSFUL_CHECKIN => "You have successfully checked-in",
        self::CHECKIN_FIRST => "You've to checkin first",
        self::ALREADY_DEVICE_USED => "This device is already used in another account today",
        self::OUT_OF_GEO_LOCATION => "You are not inside of Geo Location coverage area!",
        self::OUT_OF_WIFI_GEO_LOCATION => "You're not in specified location channels. Please move your position and try again",
    ];
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return self::$MESSAGES[$this->code];
    }

    public function isSuccess()
    {
        return in_array($this->code, [self::SUCCESSFUL, self::LATE_TODAY, self::LEFT_EARLY_TODAY]);
    }

    public function isFailed()
    {
        return !$this->isSuccess();
    }

    public function isNoteRequired()
    {
        return in_array($this->code, [self::LATE_TODAY, self::LEFT_EARLY_TODAY]);
    }

}
