<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


class ActionResultCodeMessages
{
    const LATE_FOR_TODAY = "You're late for today";
    const OUT_OF_WIFI_AREA = "You're out of our wifi area";
    const DEVICE_UNAUTHORIZED = "This device is authorized";
    const ALREADY_CHECKED_IN = "You've already checked in";
    const ALREADY_CHECKED_OUT = "You've already checked out";
    const SUCCESSFUL = "Successful";
    const CHECKIN_FIRST = "You've to checkin first";
    const ALREADY_DEVICE_USED = "This device is already used in another account today";
}