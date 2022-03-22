<?php namespace App\Sheba\Business\Attendance\Setting;

class OfficeLocationFormatter
{
    private $businessOffices;

    public function __construct($business_offices)
    {
        $this->businessOffices = $business_offices->where('is_location', 1);
    }

    public function get($editable_id = null)
    {
        $office_locations = [];
        foreach ($this->businessOffices as $business_office)
        {
            $location = $business_office->location;
            $office_locations[] = [
                'id' => $business_office->id,
                'location_name' => $business_office->name,
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'radius' => intval($location['radius']),
                'is_location' => $business_office->is_location,
                'is_editable' => $editable_id == $business_office->id ? 1 : 0
            ];
        }
        return $office_locations;
    }
}
