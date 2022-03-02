<?php namespace Sheba\Business\Attendance\Setting;

class AttendanceSettingTransformer
{
    public function getData($attendance_types, $business_offices)
    {
        $attendance_setting_info = [];

        $attendance_setting_info['sheba_attendance_types'] = $this->getShebaAttendanceTypes();
        $attendance_setting_info['attendance_types'] = $this->getAttendanceTypes($attendance_types);
        $attendance_setting_info['business_offices'] = $this->getOfficeNamesWithIp($business_offices);

        return $attendance_setting_info;
    }

    private function getAttendanceTypes($attendance_types)
    {
        $attendance_types_data = [];
        foreach ( $attendance_types as $attendance_type )
        {
            array_push($attendance_types_data,[
                'id' => $attendance_type->id,
                'type' => $attendance_type->attendance_type,
                'status' => $attendance_type->trashed() ? 'deleted' : 'not_deleted'
            ]);
        }

        return $attendance_types_data;
    }

    private function getOfficeNamesWithIp($business_offices)
    {
        $office_names_with_ip = [];
        foreach ($business_offices as $business_office)
        {
            array_push($office_names_with_ip,[
                'id' => $business_office->id,
                'office_name' => $business_office->name,
                'ip' => $business_office->ip
            ]);
        }

        return $office_names_with_ip;
    }

    private function getShebaAttendanceTypes()
    {
        return [
            [ 'value' => 'ip_based' , 'title' => 'Wifi based', 'subtitle' => ' - from office Wi-Fi network only' ],
            [ 'value' => 'remote', 'title' => 'Remote', 'subtitle' => ' - from anywhere' ],
            [ 'value' => 'geo', 'title' => 'Locations Based', 'subtitle' => ' - from office location zone' ]
        ];
    }
}
