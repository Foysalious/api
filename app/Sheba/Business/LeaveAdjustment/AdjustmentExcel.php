<?php namespace Sheba\Business\LeaveAdjustment;


class AdjustmentExcel
{
    const SHEET = "data";

    const USERS_MAIL_COLUMN = "A";
    const TITLE_COLUMN = "B";
    const LEAVE_TYPE_ID_COLUMN = "C";
    const START_DATE_COLUMN = "D";
    const END_DATE_COLUMN = "E";
    const NOTE_COLUMN = "F";
    const IS_HALF_DAY_COLUMN = "G";
    const HALF_DAY_CONFIGURATION_COLUMN = "H";
    const APPROVER_ID_COLUMN = "I";
    const MESSAGE_COLUMN = "J";

    const LEAVE_TYPE_ID = "L";
    const LEAVE_TYPE_TITLE = "M";
    const TOTAL_DAYS = "N";

    const USERS_MAIL_COLUMN_TITLE = "users_email";
    const TITLE_COLUMN_TITLE = "title";
    const LEAVE_TYPE_ID_COLUMN_TITLE = "leave_type_id";
    const START_DATE_COLUMN_TITLE = "start_date";
    const END_DATE_COLUMN_TITLE = "end_date";
    const NOTE_COLUMN_TITLE = "note";
    const IS_HALF_DAY_COLUMN_TITLE = "is_half_day";
    const HALF_DAY_CONFIGURATION_COLUMN_TITLE = "half_day_configuration";
    const APPROVER_ID_COLUMN_TITLE = "approver_id";
    const MESSAGE_COLUMN_TITLE = "message";
}