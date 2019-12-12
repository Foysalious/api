<?php namespace Sheba\Notification\B2b;

use Illuminate\Support\Facades\Mail;

class TripRequests
{

    private $member;
    private $businessTripRequest;
    private $businessMember;
    private $notificationTitle;

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

    public function getRequesterIdentity()
    {
        $identity = $this->member->profile->name;
        if (!$identity) $identity = $this->member->profile->mobile;
        if (!$identity) $identity = $this->member->profile->email;
        if (!$identity) $identity = 'ID: ' . $this->member->profile->id;
        return $identity;
    }

    public function notify($mail = true)
    {
        notify($this->businessMember->member)->send([
            'title' => $this->notificationTitle,
            'event_type' => get_class($this->businessTripRequest),
            'event_id' => $this->businessTripRequest->id
        ]);
        if ($mail) $this->sendEmail();
    }

    private function sendEmail()
    {
        $identity = $this->getRequesterIdentity();
        $template = 'emails.trip_request_create_notifications';
        $subject = 'New Trip Request';
        $title = "$identity has created a new trip request.";
        $trip_requester = $identity;
        $trip_pickup_address = $this->businessTripRequest->pickup_address;
        $trip_dropoff_address = $this->businessTripRequest->dropoff_address;
        $trip_request_created_at = $this->businessTripRequest->created_at->format('jS F, Y g:i A');
        #$email = $this->>businessMember->member->profile->email;
        $email = 'saiful.sheba@gmail.com';
        $link = config('sheba.b2b_url') . "/dashboard/fleet-management/requests/" . $this->businessTripRequest->id . "/details";
        Mail::send($template, [
            'title' => $title,
            'trip_requester' => $trip_requester,
            'trip_pickup_address' => $trip_pickup_address,
            'trip_dropoff_address' => $trip_dropoff_address,
            'trip_request_created_at' => $trip_request_created_at,
            'link' => $link
        ], function ($m) use ($subject, $email) {
            $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
            $m->to($email)->subject($subject);
        });
    }

}