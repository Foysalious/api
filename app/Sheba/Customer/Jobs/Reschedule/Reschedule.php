<?php


namespace Sheba\Customer\Jobs\Reschedule;


use App\Models\Customer;
use App\Models\Job;
use App\Models\Order;
use GuzzleHttp\Client;
use Sheba\AutoSpAssign\Job\InitiateAutoSpAssign;
use Sheba\Jobs\JobTime;
use Sheba\Jobs\PreferredTime;
use Sheba\Customer\Jobs\Reschedule\RescheduleResponse;
use Sheba\Location\Geo;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\PartnerListBuilder;
use Sheba\PushNotificationHandler;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\UserAgentInformation;

class Reschedule
{
    /** @var Job */
    private $job;
    /** @var UserAgentInformation */
    private $userAgentInformation;

    private $scheduleDate;
    private $scheduleTimeSlot;
    private $customer;
    private $partnerListBuilder;
    private $serviceRequestObject;
    private $serviceRequest;
    private $partnerListDirector;

    public function __construct(ServiceRequest $serviceRequest, PartnerListBuilder $partnerListBuilder, Director $director)
    {
        $this->partnerListBuilder = $partnerListBuilder;
        $this->serviceRequest = $serviceRequest;
        $this->partnerListDirector = $director;
    }

    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }



    /**
     * @param Job $job
     * @return Reschedule
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }



    /**
     * @param Customer $customer
     * @return Reschedule
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }
    /**
     * @param mixed $scheduleDate
     * @return Reschedule
     */
    public function setScheduleDate($scheduleDate)
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    /**
     * @param mixed $scheduleTime
     * @return Reschedule
     */
    public function setScheduleTimeSlot($scheduleTime)
    {
        $this->scheduleTimeSlot = $scheduleTime;
        return $this;
    }

    public function reschedule()
    {
        $job_time = new JobTime($this->scheduleDate, $this->scheduleTimeSlot);
        $response = new RescheduleResponse();
        if (!$job_time->validate()) return $response->setCode(400)->setMessage($job_time->error_message);

        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $this->job->id . '/customer-reschedule',
            [
                'form_params' => [
                    'customer_id' => $this->customer->id,
                    'remember_token' => $this->customer->remember_token,
                    'schedule_date' => $this->scheduleDate,
                    'preferred_time' => $this->scheduleTimeSlot,
                    'portal_name' => $this->userAgentInformation->getPortalName(),
                    'user_agent' => $this->userAgentInformation->getUserAgent(),
                    'ip' => $this->userAgentInformation->getIp()
                ]
            ]);

        $response = $response->setResponse(json_decode($res->getBody(), 1))->getResponse();
        if($response['code'] === 421) {
            $this->resetJob();
            $this->initialAutoSPAssignOnExistingJob();
            $response['code'] = 200;
            $response['msg'] = "Order Rescheduled Successfully!";
            $response['job_id'] = $this->job->id;
        } else {
            $this->notifyPartnerAboutReschedule();
        }

        return $response;
    }

    private function notifyPartnerAboutReschedule()
    {
        $partner = $this->job->partnerOrder->partner;
        if(!$partner) return;
        $sender_id = $this->job->partnerOrder->order->customer_id;
        $sender_type = 'customer';

        notify()->partner($partner->id)->sender($sender_id, $sender_type)->send([
            'title'      => 'Order Reschedule ID ' . $this->job->partnerOrder->code(),
            'link'       => null,
            'type'       => notificationType('Info'),
            'event_type' => "App\Models\PartnerOrder",
            'event_id'   => $this->job->partnerOrder->id,
            //'version' => $partner_order->getVersion()
        ]);
        $topic   = config('sheba.push_notification_topic_name.manager') . $this->job->partnerOrder->partner_id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound   = config('sheba.push_notification_sound.manager');
        (new PushNotificationHandler())->send([
            "title"      => 'Order Reschedule',
            "message"    => "আপনার ". $this->job->partnerOrder->code() . " অর্ডার টি শিডিউল পরিবর্তন হয়েছে, রিসোর্স আসাইন করুন",
            "event_type" => 'PartnerOrder',
            "event_id"   => $this->job->partnerOrder->id,
            "link"       => null,
            "sound"      => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);

    }

    private function initialAutoSPAssignOnExistingJob()
    {
        $partnerOrder = $this->job->partnerOrder;
        $customer = $partnerOrder->order->customer;

        $deliveryAddress = $customer->delivery_addresses()->withTrashed()->where('id', $partnerOrder->order->delivery_address_id)->first();
        $geo = $deliveryAddress->getGeo();

        $services = $this->formatServicesForOrder($this->job->jobServices);
        $this->serviceRequestObject = $this->serviceRequest
            ->setServices($services)->get();

        $this->partnerListBuilder
            ->setGeo($geo)
            ->setServiceRequestObjectArray($this->serviceRequestObject)
            ->setScheduleTime($this->scheduleTimeSlot)
            ->setScheduleDate($this->scheduleDate);

        $this->partnerListDirector->setBuilder($this->partnerListBuilder)->buildPartnerListForOrderPlacement();

        $partnersFromList = $this->partnerListBuilder->get();

        if($partnersFromList->count() > 0) dispatch(new InitiateAutoSpAssign($this->job->partnerOrder, $customer, $partnersFromList->pluck('id')->toArray()));
    }

    private function formatServicesForOrder($jobServices)
    {
        $carRentalJobDetail = $this->job->carRentalJobDetail;
        $services = [];
        $jobServices->each(function ($service, $key) use (&$services, $carRentalJobDetail){
            $s = [
                'id' => $service->service_id,
                'quantity' => $service->quantity,
                'option' => json_decode($service->option),
            ];

            if($carRentalJobDetail) {
                $s['pick_up_location_geo'] = json_decode($carRentalJobDetail->pick_up_address_geo, 1);
                $s['destination_location_geo'] = json_decode($carRentalJobDetail->destination_address_geo, 1);
            }

            array_push($services, $s);
        });

        return $services;
    }

    private function resetJob() {
        /** @var  Order $order */
        $order = $this->job->partnerOrder->order;

        
        $this->job = $order->lastJob();
    }

}
