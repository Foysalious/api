<?php namespace App\Sheba\Business;

use App\Models\Business;
use App\Models\BusinessTrip;
use Carbon\Carbon;
use Sheba\Business\BusinessSmsHandler;


class BusinessTripSms
{
    private $businessTrip;
    /** @var Business */
    private $business;
    private $mobile;
    private $vehicleName;
    private $arrivalTime;
    private $cost;

    public function setTrip(BusinessTrip $business_trip)
    {
        $this->businessTrip = $business_trip;
        $this->business = $this->businessTrip->business;
        $this->mobile = $this->businessTrip->member->profile->mobile;
        $this->vehicleName = $this->businessTrip->vehicle->basicInformation->company_name;
        $this->arrivalTime = Carbon::parse($this->businessTrip->start_date)->format('Y-m-d H:i:s');
        $this->cost = 0.25;
        return $this;
    }

    private function getEvent($event_name)
    {
        return $this->business->businessSmsTemplates()->where('event_name', $event_name)->first();
    }

    public function sendTripRequestAccept()
    {
        if ($this->businessTrip->business->wallet >= $this->cost && $this->getEvent('trip_request_accept')) {
            (new BusinessSmsHandler('trip_request_accept'))->send($this->mobile, [
                'vehicle_name' => $this->vehicleName,
                'arrival_time' => $this->arrivalTime,
            ]);
            $this->business->debitWallet($this->cost);
            $this->business->walletTransaction(['amount' => $this->cost, 'type' => 'Debit', 'log' => 'Sms send', 'tag' => 'sms']);
        }
    }
}