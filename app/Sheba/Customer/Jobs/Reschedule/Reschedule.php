<?php


namespace Sheba\Customer\Jobs\Reschedule;


use App\Models\Customer;
use App\Models\Job;
use GuzzleHttp\Client;
use Sheba\AutoSpAssign\Job\InitiateAutoSpAssign;
use Sheba\Jobs\JobTime;
use Sheba\Jobs\PreferredTime;
use Sheba\Customer\Jobs\Reschedule\RescheduleResponse;
use Sheba\Location\Geo;
use Sheba\PartnerList\PartnerListBuilder;
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

    public function __construct(ServiceRequest $serviceRequest, PartnerListBuilder $partnerListBuilder)
    {
        $this->partnerListBuilder = $partnerListBuilder;
        $this->serviceRequest = $serviceRequest;
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
        $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $this->job->id . '/reschedule',
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
        $response->setResponse(json_decode($res->getBody(), 1))->getResponse();

        if($response['code'] === 421) {
            $this->initialAutoSPAssignOnExistingJob();
        }

        return $response;
    }

    private function initialAutoSPAssignOnExistingJob()
    {
        $partnerOrder = $this->job->partnerOrder;
        $customer = $partnerOrder->order->customer;

        $deliveryAddress = $customer->delivery_addresses()->withTrashed()->where('id', $partnerOrder->order->delivery_address_id)->first();
        $geo = new Geo();
        $geo->setLng($deliveryAddress->geo->lng)->setLat($deliveryAddress->geo->lat);
        $services = [];
        $this->job->jobServices->each(function ($service, $key) use (&$services){
            array_push($services, [
                'id' => $service->service_id,
                'quantity' => $service->quantity,
                'option' => json_decode($service->option),
            ]);
        });

        $this->serviceRequestObject = $this->serviceRequest
            ->setServices($services)->get();

        $this->partnerListBuilder
            ->setGeo($geo)
            ->setServiceRequestObjectArray($this->serviceRequestObject)
            ->setScheduleTime($this->scheduleTimeSlot)
            ->setScheduleDate($this->scheduleDate);

        $partnersFromList = $this->partnerListBuilder->get();

        if($partnersFromList) dispatch(new InitiateAutoSpAssign($this->job->partnerOrder, $customer, $partnersFromList->pluck('id')->toArray()));
    }

}