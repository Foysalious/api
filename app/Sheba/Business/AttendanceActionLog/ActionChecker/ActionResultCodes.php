<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


class ActionResultCodes
{
    const LATE_TODAY = 501;
    const LEFT_EARLY_TODAY = 508;
    const OUT_OF_WIFI_AREA = 502;
    const DEVICE_UNAUTHORIZED = 503;
    const ALREADY_CHECKED_IN = 504;
    const ALREADY_CHECKED_OUT = 505;
    const CHECKIN_FIRST = 506;
    const ALREADY_DEVICE_USED = 507;
    const SUCCESSFUL = 200;

}