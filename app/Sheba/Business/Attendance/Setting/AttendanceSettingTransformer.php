<?php namespace Sheba\Business\Attendance\Setting;

use App\Sheba\Business\Attendance\Setting\OfficeIpFormatter;
use App\Sheba\Business\Attendance\Setting\OfficeLocationFormatter;

class AttendanceSettingTransformer
{
    public function getData($attendance_types, $business_offices)
    {
        $attendance_setting_info = [];

        $attendance_setting_info['sheba_attendance_types'] = $this->getShebaAttendanceTypes();
        $attendance_setting_info['attendance_types'] = $this->getAttendanceTypes($attendance_types);
        $attendance_setting_info['business_offices'] = (new OfficeIpFormatter($business_offices))->get();
        $attendance_setting_info['business_geo_locations'] = (new OfficeLocationFormatter($business_offices))->get();

        return $attendance_setting_info;
    }

    private function getAttendanceTypes($attendance_types)
    {
        $attendance_types_data = [];
        foreach ( $attendance_types as $attendance_type )
        {
            $attendance_types_data[] = [
                'id' => $attendance_type->id,
                'type' => $attendance_type->attendance_type,
                'status' => $attendance_type->trashed() ? 'deleted' : 'not_deleted'
            ];
        }
        return $attendance_types_data;
    }

    private function getShebaAttendanceTypes()
    {
        return [
            [ 'value' => 'ip_based' , 'title' => 'Wifi based', 'subtitle' => ' - from office Wi-Fi network only' ],
            [ 'value' => 'remote', 'title' => 'Remote', 'subtitle' => ' - from anywhere' ],
            [ 'value' => 'location_based', 'title' => 'Locations Based', 'subtitle' => ' - from office location zone' ]
        ];
    }
}
