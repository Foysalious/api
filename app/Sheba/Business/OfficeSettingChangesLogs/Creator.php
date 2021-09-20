<?php namespace App\Sheba\Business\OfficeSettingChangesLogs;

use Sheba\Dal\OfficeSettingChangesLogs\OfficeSettingChangesLogsRepository;

class Creator
{
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

    }

    public function createWeekendLogs()
    {
        $previous_weekend = $this->officeSettingChangesLogsRequester->getPreviousWeekends();
        sort($previous_weekend);
        $new_weekend = $this->officeSettingChangesLogsRequester->getNewWeekends();
        sort($new_weekend);
        $previous_weekend_string = implode(', ', array_map('ucfirst', $previous_weekend));
        $new_weekend_string = implode(', ', array_map('ucfirst', $new_weekend));
        if ($previous_weekend == $new_weekend) return;
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

}