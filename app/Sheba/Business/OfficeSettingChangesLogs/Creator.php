<?php namespace App\Sheba\Business\OfficeSettingChangesLogs;

use App\Sheba\Business\Attendance\AttendanceBasicInfo;
use Sheba\Dal\OfficeSettingChangesLogs\OfficeSettingChangesLogsRepository;

class Creator
{
    use AttendanceBasicInfo;
    /*** @var Requester */
    private $officeSettingChangesLogsRequester;
    /*** @var OfficeSettingChangesLogsRepository */
    private $officeSettingChangesLogsRepo;

    public function __construct()
    {
        $this->officeSettingChangesLogsRepo = app(OfficeSettingChangesLogsRepository::class);
        return $this;
    }

    public function setOfficeSettingChangesLogsRequester(Requester $office_setting_changes_logs_requester)
    {
        $this->officeSettingChangesLogsRequester = $office_setting_changes_logs_requester;
        return $this;
    }

    public function createWorkingDaysTypeLogs()
    {
        $previous_working_days_type = $this->officeSettingChangesLogsRequester->getPreviousTotalWorkingDaysType();
        $previous_number_of_days = $this->officeSettingChangesLogsRequester->getPreviousNumberOfDays();
        $previous_is_included_weekend = $this->officeSettingChangesLogsRequester->getPreviousIsWeekendIncluded();
        $new_working_days_type = $this->officeSettingChangesLogsRequester->getNewWorkingDaysType();
        $new_number_of_days = $this->officeSettingChangesLogsRequester->getNewNumberOfDays();
        $new_is_included_weekend = $this->officeSettingChangesLogsRequester->getNewIsWeekendIncluded();
        if ($previous_working_days_type == $new_working_days_type && $previous_is_included_weekend == $new_is_included_weekend && $previous_number_of_days == $new_number_of_days) return;
        $logs = $from = $to = "";

        if ($previous_working_days_type != $new_working_days_type && $new_working_days_type == 'fixed') {
            $previous_working_days_type = 'As per calender';
            $new_working_days_type = 'Fixed';
            $previous_is_included_weekend = $previous_is_included_weekend == 0 ? 'excluding weekends' : 'including weekends';
            $from = $previous_working_days_type.", ".$previous_is_included_weekend;
            $to = $new_working_days_type.", ".$new_number_of_days;
            $logs = "Total working days changed from ".$previous_working_days_type.", ".$previous_is_included_weekend." to ".$new_working_days_type." ".$new_number_of_days." days ";
        }
        else if ($previous_working_days_type != $new_working_days_type && $new_working_days_type == 'as_per_calendar') {
            $previous_working_days_type = 'Fixed';
            $new_working_days_type = 'As per calender';
            $new_is_included_weekend = $new_is_included_weekend == 0 ? 'excluding weekends' : 'including weekends';
            $from = $previous_working_days_type.", ".$new_number_of_days;
            $to = $new_working_days_type.", ".$new_is_included_weekend;
            $logs = "Total working days changed from ".$previous_working_days_type.", ".$previous_number_of_days." days to ".$new_working_days_type." ".$new_is_included_weekend;
        }
        else if ($previous_working_days_type == $new_working_days_type && $new_working_days_type == 'as_per_calendar') {
            $previous_working_days_type = 'As per calender';
            $new_working_days_type = 'As per calender';
            $previous_is_included_weekend = $previous_is_included_weekend == 0 ? 'excluding weekends' : 'including weekends';
            $new_is_included_weekend = $new_is_included_weekend == 0 ? 'excluding weekends' : 'including weekends';
            $from = $previous_working_days_type.", ".$previous_is_included_weekend;
            $to = $new_working_days_type.", ".$new_is_included_weekend;
            $logs = "Total working days changed from ".$previous_working_days_type.", ".$previous_is_included_weekend." to ".$new_working_days_type." ".$new_is_included_weekend;
        }
        else if ($previous_working_days_type == $new_working_days_type && $new_working_days_type == 'fixed') {
            $previous_working_days_type = 'Fixed';
            $new_working_days_type = 'Fixed';
            $from = $previous_working_days_type.", ".$previous_number_of_days;
            $to = $new_working_days_type.", ".$new_number_of_days;
            $logs = "Total working days changed from ".$previous_working_days_type.", ".$previous_number_of_days." days to ".$new_working_days_type." ".$new_number_of_days." days ";
        }

        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'operational',
            'from' => $from,
            'to' => $to,
            'logs' => $logs
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);

    }

    public function createWeekendLogs()
    {
        $previous_weekend = $this->officeSettingChangesLogsRequester->getPreviousWeekends();
        $new_weekend = $this->officeSettingChangesLogsRequester->getNewWeekends();
        $previous_weekend_string = $this->getFormattedWeekendsString($previous_weekend);
        $new_weekend_string = $this->getFormattedWeekendsString($new_weekend);
        if ($previous_weekend_string == $new_weekend_string) return;
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'operational',
            'from' => $previous_weekend_string,
            'to' => $new_weekend_string,
            'logs' => 'Weekend updated from '.$previous_weekend_string.' to '.$new_weekend_string
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createAttendanceTypeLogs()
    {
        $previous_type = $this->officeSettingChangesLogsRequester->getPreviousAttendanceType();
        $new_type = $this->officeSettingChangesLogsRequester->getNewAttendanceType();
        if ($previous_type == $new_type) return;
        $previous_type_string = implode(", ", $previous_type);
        $new_type_string = implode(", ", $new_type);
        $previous_type_string = str_replace('remote','Remote', $previous_type_string);
        $previous_type_string = str_replace('ip_based', 'WiFi', $previous_type_string);
        $new_type_string = str_replace('remote','Remote', $new_type_string);
        $new_type_string = str_replace('ip_based', 'WiFi', $new_type_string);
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'operational',
            'from' => $previous_type_string,
            'to' => $new_type_string,
            'logs' => 'Attendance Type updated from '.$previous_type_string.' to '.$new_type_string
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createDeleteOfficeIpLogs()
    {
        $office_ip = $this->officeSettingChangesLogsRequester->getOfficeIp();
        $office_name = $this->officeSettingChangesLogsRequester->getOfficeName();
        if (!$office_ip && !$office_name) return;
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'operational',
            'logs' => 'Office IP '.$office_ip.' - '.$office_name.' has been deleted'
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createCreatedOfficeIpLogs()
    {
        $office_ip = $this->officeSettingChangesLogsRequester->getOfficeIp();
        $office_name = $this->officeSettingChangesLogsRequester->getOfficeName();
        if (!$office_ip && !$office_name) return;
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'operational',
            'logs' => 'New Office IP '.$office_ip.' - '.$office_name.' has been added'
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createEditedOfficeIpLogs()
    {
        $previous_office_ip = $this->officeSettingChangesLogsRequester->getPreviousOfficeIp();
        $office_ip = $this->officeSettingChangesLogsRequester->getOfficeIp();
        $office_name = $this->officeSettingChangesLogsRequester->getOfficeName();
        if (!$office_ip && !$office_name) return;
        $from = $previous_office_ip->ip. ' - '.$previous_office_ip->name;
        $to = $office_ip. ' - '.$office_name;
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'operational',
            'from' => $from,
            'to' => $to,
            'logs' => 'Office IP has been updated from '.$from.' to '.$to
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createHolidayStoreLogs()
    {
        $holiday_start = $this->officeSettingChangesLogsRequester->getHolidayStartDate();
        $holiday_end = $this->officeSettingChangesLogsRequester->getHolidayEndDate();
        $holiday_name = $this->officeSettingChangesLogsRequester->getHolidayName();
        $logs = $holiday_start != $holiday_end ? "New holiday ".$holiday_name." from ".$holiday_start." to ".$holiday_end." has been added" : "New holiday ".$holiday_name." on ".$holiday_start." has been added";
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'holiday',
            'logs' => $logs
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createHolidayDeleteLogs()
    {
        $existing_holiday = $this->officeSettingChangesLogsRequester->getExistingHoliday();
        $holiday_start = $existing_holiday->start_date->format('Y-m-d');
        $holiday_end = $existing_holiday->end_date->format('Y-m-d');
        $holiday_name = $existing_holiday->title;
        $logs = $holiday_start != $holiday_end ? "Holiday ".$holiday_name." from ".$holiday_start." to ".$holiday_end." has been removed" : "Holiday ".$holiday_name." on ".$holiday_start." has been removed";
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'type' => 'holiday',
            'logs' => $logs
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

    public function createHolidayUpdateLogs()
    {
        $prev_holiday_start  = $this->officeSettingChangesLogsRequester->getExistingHolidayStart()->format('Y-m-d');
        $prev_holiday_end  = $this->officeSettingChangesLogsRequester->getExistingHolidayEnd()->format('Y-m-d');
        $prev_holiday_name = $this->officeSettingChangesLogsRequester->getExistingHolidayName();
        $new_start_date = $this->officeSettingChangesLogsRequester->getHolidayStartDate();
        $new_end_date = $this->officeSettingChangesLogsRequester->getHolidayEndDate();
        $new_holiday_name = $this->officeSettingChangesLogsRequester->getHolidayName();
        if ($prev_holiday_start == $new_start_date && $prev_holiday_end == $new_end_date && $prev_holiday_name == $new_holiday_name) return;
        $logs = "Holiday updated from ".$prev_holiday_name." on (".$prev_holiday_start." - ".$prev_holiday_end.") to ".$new_holiday_name." on (".$new_start_date." - ".$new_end_date.")";
        $log_data = [
            'business_id' => $this->officeSettingChangesLogsRequester->getBusiness()->id,
            'from' => $prev_holiday_name.", (".$prev_holiday_start." - ".$prev_holiday_end.")",
            'to' => $new_holiday_name.", (".$new_start_date." - ".$new_end_date.")",
            'type' => 'holiday',
            'logs' => $logs
        ];
        $this->officeSettingChangesLogsRepo->create($log_data);
    }

}