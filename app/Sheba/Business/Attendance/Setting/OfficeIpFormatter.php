<?php namespace App\Sheba\Business\Attendance\Setting;

class OfficeIpFormatter
{
    private $businessOffices;

    public function __construct($business_offices)
    {
        $this->businessOffices = $business_offices->where('is_location', 0);
    }

    public function get()
    {
        $office_names_with_ip = [];
        foreach ($this->businessOffices as $business_office)
        {
            $office_names_with_ip[] = [
                'id' => $business_office->id,
                'office_name' => $business_office->name,
                'ip' => $business_office->ip
            ];
        }
        return $office_names_with_ip;
    }

}
