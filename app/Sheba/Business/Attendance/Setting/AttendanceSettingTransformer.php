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

    public function getEmployeePolicyData($attendance_types, $business_offices)
    {
        $attendance_policy_data = [];
        $attendance_policy_data['attendance_type'] = $this->getAttendancePolicyTypes($attendance_types);
        $attendance_policy_data['office_ip'] = $this->getOfficeNamesWithIp($business_offices);
        return $attendance_policy_data;

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

    private function getAttendancePolicyTypes($attendance_types)
    {
        $policy_types_data = [];
        $is_remote_enable = 0;
        $is_wifi_enable = 0;
        foreach ( $attendance_types as $attendance_type )
        {
            if ($attendance_type->trashed()) continue;
            $type = $attendance_type->attendance_type;
            if($type == 'remote') $is_remote_enable = 1;
            if($type == 'ip_based') $is_wifi_enable = 1;
            array_push($policy_types_data,
                $type == 'remote' ? 'Remote' : 'Office WiFi'
            );
        }
        $policy_types['types'] = $policy_types_data;
        $policy_types['is_remote_enable'] = $is_remote_enable;
        $policy_types['is_wifi_enable'] = $is_wifi_enable;
        return $policy_types;
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
            [ 'value' => 'ip_based' , 'title' => 'IP based attendance', 'subtitle' => ' - from office Wi-Fi network only' ],
            [ 'value' => 'remote', 'title' => 'Remote attendance', 'subtitle' => ' - from anywhere' ]
        ];
    }
}
