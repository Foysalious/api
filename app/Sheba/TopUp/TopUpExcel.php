<?php namespace Sheba\TopUp;

class TopUpExcel
{
    const SHEET = "data";
    const SHEET_INDEX = 0;

    const MOBILE_COLUMN = "A";
    const VENDOR_COLUMN = "B";
    const TYPE_COLUMN = "C";
    const AMOUNT_COLUMN = "D";
    const STATUS_COLUMN = "E";
    const NAME_COLUMN = "F";
    const CREATED_DATE_COLUMN = "G";
    const MESSAGE_COLUMN = "H";

    const MOBILE_COLUMN_TITLE = "mobile";
    const VENDOR_COLUMN_TITLE = "operator";
    const TYPE_COLUMN_TITLE = "connection_type";
    const AMOUNT_COLUMN_TITLE = "amount";
    const STATUS_COLUMN_TITLE = "status";
    const NAME_COLUMN_TITLE = "name";
    const CREATED_DATE_TITLE = "created_date";
    const MESSAGE_COLUMN_TITLE = "message";

    const MOBILE_COLUMN_INDEX = 0;
    const VENDOR_COLUMN_INDEX = 1;
    const TYPE_COLUMN_INDEX = 2;
    const AMOUNT_COLUMN_INDEX = 3;
}
