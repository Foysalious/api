<?php namespace Sheba\Business\LeaveAdjustment;


class AdjustmentExcel
{
    const SHEET = "data";

    const USERS_MAIL_COLUMN = "A";
    const LEAVE_TYPE_ID_COLUMN = "B";
    const START_DATE_COLUMN = "C";
    const END_DATE_COLUMN = "D";
    const NOTE_COLUMN = "E";
    const IS_HALF_DAY_COLUMN = "F";
    const HALF_DAY_CONFIGURATION_COLUMN = "G";
    const APPROVER_ID_COLUMN = "H";
    const MESSAGE_COLUMN = "I";

    const LEAVE_TYPE_ID = "K";
    const LEAVE_TYPE_TITLE = "L";
    const TOTAL_DAYS = "M";

    const SUPER_ADMIN_ID = "O";
    const SUPER_ADMIN_NAME = "P";

    const USERS_MAIL_COLUMN_TITLE = "users_email";
    const LEAVE_TYPE_ID_COLUMN_TITLE = "leave_type_id";
    const START_DATE_COLUMN_TITLE = "start_date";
    const END_DATE_COLUMN_TITLE = "end_date";
    const NOTE_COLUMN_TITLE = "note";
    const IS_HALF_DAY_COLUMN_TITLE = "is_half_day";
    const HALF_DAY_CONFIGURATION_COLUMN_TITLE = "half_day_configuration";
    const APPROVER_ID_COLUMN_TITLE = "approver_id";
    const MESSAGE_COLUMN_TITLE = "message";
}