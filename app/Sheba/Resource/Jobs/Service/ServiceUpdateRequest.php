<?php namespace Sheba\Resource\Jobs\Service;

use App\Models\Job;
use Sheba\Dal\JobService\JobService;
use Sheba\Resource\Jobs\Response;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\Resource\Jobs\Material\Creator as MaterialCreator;
use DB;
use Sheba\UserAgentInformation;

class ServiceUpdateRequest
{
    /** @var array */
    private $newServices;
    /** @var array */
    private $quantity;
    /** @var array */
    private $materials;
    /** @var Job */
    private $job;
    private $serviceRequest;
    private $createNewJobService;
    private $materialCreator;
    private $updater;
    private $policy;
    private $response;
    private $userAgentInformation;

    public function __construct(ServiceRequest $serviceRequest, Creator $create_new_job_service, MaterialCreator $material_creator, Updater $updater, ServiceUpdateRequestPolicy $policy, Response $response)
    {
        $this->serviceRequest = $serviceRequest;
        $this->createNewJobService = $create_new_job_service;
        $this->materialCreator = $material_creator;
        $this->updater = $updater;
        $this->policy = $policy;
        $this->response = $response;
    }


    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param Job $job
     * @return ServiceUpdateRequest
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }


    /**
     * @param array $services
     * @return $this
     * @throws \App\Exceptions\RentACar\DestinationCitySameAsPickupException
     * @throws \App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException
     * @throws \App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException
     */
    public function setServices(array $services)
    {
        $this->newServices = $this->serviceRequest->setServices($services)->get();
        return $this;
    }


    /**
     * @param $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }


    /**
     * @param $materials
     * @return $this
     */
    public function setMaterials($materials)
    {
        $this->materials = $materials;
        return $this;
    }

    /**
     * @return Response
     */
    public function update()
    {
        if (!$this->policy->setJob($this->job)->setPartner($this->job->partnerOrder->partner)->canUpdate()) {
            $this->response->setCode(403)->setMessage('আপনার এই প্রক্রিয়া টি সম্পন্ন করা সম্ভব নয়, অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন');
            return $this->response;
        }
        DB::transaction(function () {
            if (count($this->newServices) > 0) $this->createNewJobService->setJob($this->job)->setServices($this->newServices)->create();
            if (count($this->materials) > 0) $this->materialCreator->setJob($this->job)->setUserAgentInformation($this->userAgentInformation)->setMaterials($this->materials)->create();
            if (count($this->quantity) > 0) {
                foreach ($this->quantity as $quantity) {
                    $job_service = JobService::find($quantity['job_service_id']);
                    $this->updater->setJobService($job_service)->setQuantity($quantity['quantity'])->update();
                }
            }

            $this->response->setSuccessfulCode()->setSuccessfulMessage();
        });
        return $this->response;
    }


}