<?php namespace App\Sheba\Business\AttendanceReconciliation\BulkReconciliation;

class AttendanceReconciliationExcel
{
    const SHEET = "data";

    const EMPLOYEE_ID_COLUMN = "A";
    const EMPLOYEE_EMAIL_COLUMN = "B";
    const RECONCILIATION_DATE_COLUMN = "C";
    const ATTENDANCE_CHECKIN_COLUMN = "D";
    const ATTENDANCE_CHECKOUT_COLUMN = "E";
    const MESSAGE_COLUMN = "F";

    const EMPLOYEE_ID_COLUMN_TITLE = "employee_id";
    const EMPLOYEE_EMAIL_COLUMN_TITLE = "email";
    const RECONCILIATION_DATE_COLUMN_TITLE = "date";
    const ATTENDANCE_CHECKIN_COLUMN_TITLE = "clockin";
    const ATTENDANCE_CHECKOUT_COLUMN_TITLE = "clockout";
    const MESSAGE_COLUMN_TITLE = "message";

}