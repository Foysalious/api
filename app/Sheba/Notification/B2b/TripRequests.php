<?php namespace Sheba\Notification\B2b;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Mail;

class TripRequests
{
    private $member;
    private $businessTripRequest;
    private $businessMember;
    private $notificationTitle;
    private $emailTitle;
    private $emailSubject;
    private $template;
    private $driver;
    private $vehicle;

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setBusinessTripRequest($business_trip_request)
    {
        $this->businessTripRequest = $business_trip_request;
        return $this;
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setNotificationTitle($notification_title)
    {
        $this->notificationTitle = $notification_title;
        return $this;
    }

    public function setEmailTitle($email_title)
    {
        $this->emailTitle = $email_title;
        return $this;
    }

    public function setEmailSubject($email_subject)
    {
        $this->emailSubject = $email_subject;
        return $this;
    }

    public function setEmailTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setVehicle($vehicle)
    {
        $this->vehicle = Vehicle::findOrFail((int)$vehicle);
        return $this;
    }

    public function setDriver($driver)
    {
        $this->driver = Driver::findOrFail((int)$driver);
        return $this;
    }

    public function getRequesterIdentity()
    {
        $identity = $this->member->profile->name;
        if (!$identity) $identity = $this->member->profile->mobile;
        if (!$identity) $identity = $this->member->profile->email;
        if (!$identity) $identity = 'ID: ' . $this->member->profile->id;
        return $identity;
    }


    private function getDriverProfile()
    {
        return $this->driver->profile;
    }

    private function getDriverPhoneNumber()
    {
        return $this->getDriverProfile()->mobile ? $this->getDriverProfile()->mobile : '';
    }

    private function getDriverName()
    {
        return $this->getDriverProfile()->name ? $this->getDriverProfile()->name : '';
    }

    public function notify($mail = false, $for = null)
    {
        notify($this->member)->send([
            'title' => $this->notificationTitle,
            'event_type' => get_class($this->businessTripRequest),
            'event_id' => $this->businessTripRequest->id
        ]);

        if ($mail && $for === 'TripCreate') {
            $this->mailForTripCreate();
        }
        if ($mail && $for === 'TripAccepted') {
            $this->mailForTripCreateAccepted();
        }
    }

    private function mailForTripCreate()
    {
        $link = config('sheba.b2b_url') . "/dashboard/fleet-management/requests/" . $this->businessTripRequest->id . "/details";
        #$email = $this->businessMember->member->profile->email;
        $email = 'saiful.sheba@gmail.com';
        $trip_requester = $this->getRequesterIdentity();
        $trip_pickup_address = $this->businessTripRequest->pickup_address;
        $trip_dropoff_address = $this->businessTripRequest->dropoff_address;
        $trip_request_created_at = $this->businessTripRequest->created_at->format('jS F, Y g:i A');
        Mail::send($this->template, [
            'title' => $this->emailTitle,
            'trip_requester' => $trip_requester,
            'trip_pickup_address' => $trip_pickup_address,
            'trip_dropoff_address' => $trip_dropoff_address,
            'trip_request_created_at' => $trip_request_created_at,
            'link' => $link
        ], function ($m) use ($email) {
            $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
            $m->to($email)->subject($this->emailSubject);
        });
    }

    private function mailForTripCreateAccepted()
    {
        $link = config('sheba.b2b_url') . "/dashboard/fleet-management/requests/" . $this->businessTripRequest->id . "/details";
        #$email = $this->member->profile->email;#For Trip Request Accepted
        $email = 'saiful.sheba@gmail.com';
        #$email = 'fahiman2.sheba@gmail.com';

        $trip_request_start_date = $this->businessTripRequest->start_date->format('jS F, Y g:i A');
        $vehicle_number = $this->vehicle->registrationInformations->license_number;
        $driver_name = $this->getDriverName();
        $driver_phone_number = $this->getDriverPhoneNumber();

        Mail::send($this->template, [
            'title' => $this->emailTitle,
            'driver_name' => $driver_name,
            'vehicle_number' => $vehicle_number,
            'driver_phone_number' => $driver_phone_number,
            'trip_request_start_date' => $trip_request_start_date,
            'link' => $link
        ], function ($m) use ($email) {
            $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
            $m->to($email)->subject($this->emailSubject);
        });
    }
}