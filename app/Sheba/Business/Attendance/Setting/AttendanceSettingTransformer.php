<?php namespace Sheba\Business\Attendance\Setting;

class AttendanceSettingTransformer
{
    public function getData($attendance_types, $business_offices)
    {
        $attendance_setting_info = [];

        $attendance_setting_info['sheba_attendance_types'] = $this->getShebaAttendanceTypes();
        $attendance_setting_info['attendance_types'] = $this->getAttendanceTypes($attendance_types);
        $attendance_setting_info['business_offices'] = $this->getOfficeNamesWithIp($business_offices->where('is_location', 0));
        $attendance_setting_info['business_geo_locations'] = $this->getOfficeGeoLocations($business_offices->where('is_location', 1));

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

    private function getOfficeNamesWithIp($business_offices)
    {
        $office_names_with_ip = [];
        foreach ($business_offices as $business_office)
        {
            $office_names_with_ip[] = [
                'id' => $business_office->id,
                'office_name' => $business_office->name,
                'ip' => $business_office->ip
            ];
        }
        return $office_names_with_ip;
    }

    private function getShebaAttendanceTypes()
    {
        return [
            [ 'value' => 'ip_based' , 'title' => 'Wifi based', 'subtitle' => ' - from office Wi-Fi network only' ],
            [ 'value' => 'remote', 'title' => 'Remote', 'subtitle' => ' - from anywhere' ],
            [ 'value' => 'location_based', 'title' => 'Locations Based', 'subtitle' => ' - from office location zone' ]
        ];
    }

    private function getOfficeGeoLocations($business_offices)
    {
        $office_locations = [];
        foreach ($business_offices as $business_office)
        {
            $location = $business_office->location;
            $office_locations[] = [
                'id' => $business_office->id,
                'office_name' => $business_office->name,
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'radius' => intval($location['radius']),
                'is_location' => $business_office->is_location
            ];
        }

        return $office_locations;
    }
}
