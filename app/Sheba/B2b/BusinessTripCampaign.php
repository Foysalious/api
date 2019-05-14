<?php namespace App\Sheba\B2b;

use Sheba\B2b\BusinessSmsHandler;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;
use Sheba\Sms\Sms;

class BusinessTripCampaign
{
    private $businessTrip;
    private $mobile;
    private $vehicleName;
    private $arrivalTime;

    public function setTrip(BusinessTrip $business_trip)
    {
        $this->businessTrip = $business_trip;
        $this->mobile = $this->businessTrip->member->profile->mobile;
        $this->vehicleName = $this->businessTrip->vehicle->basicInformation->company_name;
        $this->arrivalTime = $this->businessTrip->start_date;
        return $this;
    }

    public function sendSms()
    {
        (new BusinessSmsHandler('vehicle_request_accept'))->send($this->mobile, [
            'vehicle_name' => $this->vehicleName,
            'arrival_time' => $this->arrivalTime,
        ]);
    }
}