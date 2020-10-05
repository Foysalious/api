<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;


class ActionResultCodeMessages
{
    const LATE_TODAY = "Oops! You’re late today!";
    const OUT_OF_WIFI_AREA = "You are not inside our wi-fi coverage area!";
    const DEVICE_UNAUTHORIZED = "This device is unauthorized";
    const ALREADY_CHECKED_IN = "You've already checked in";
    const ALREADY_CHECKED_OUT = "You've already checked out";
    const SUCCESSFUL_CHECKOUT = "Good Bye! See you next day.";
    const SUCCESSFUL_CHECKIN = "You have successfully checked-in";
    const CHECKIN_FIRST = "You've to checkin first";
    const ALREADY_DEVICE_USED = "This device is already used in another account today";
}