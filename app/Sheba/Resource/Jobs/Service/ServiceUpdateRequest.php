<?php namespace Sheba\Resource\Jobs\Service;

use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Models\Job;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\JobMaterial\JobMaterial;
use Sheba\Dal\JobService\JobService;
use Sheba\Resource\Jobs\Response;
use Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\Resource\Jobs\Material\Creator as MaterialCreator;
use DB;
use Sheba\UserAgentInformation;
use Sheba\Resource\Jobs\Material\Updater as MaterialUpdater;

class ServiceUpdateRequest
{
    /** @var array */
    private $newServices = [];
    /** @var array */
    private $quantity = [];
    /** @var array */
    private $materials = [];

    /** @var Job */
    private $job;
    private $serviceRequest;
    private $createNewJobService;
    private $materialCreator;
    private $updater;
    private $policy;
    private $response;
    private $userAgentInformation;
    private $materialUpdater;

    public function __construct(ServiceRequest $serviceRequest, Creator $create_new_job_service, MaterialCreator $material_creator, Updater $updater, ServiceUpdateRequestPolicy $policy, Response $response, MaterialUpdater $materialUpdater)
    {
        $this->serviceRequest = $serviceRequest;
        $this->createNewJobService = $create_new_job_service;
        $this->materialCreator = $material_creator;
        $this->updater = $updater;
        $this->policy = $policy;
        $this->response = $response;
        $this->materialUpdater = $materialUpdater;
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
     * @throws DestinationCitySameAsPickupException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ValidationException
     * @throws ServiceIsUnpublishedException
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
        if (!$this->canUpdate()) {
            $this->response->setCode(403)->setMessage('আপনার এই প্রক্রিয়া টি সম্পন্ন করা সম্ভব নয়, অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন');
            return $this->response;
        }
        DB::transaction(function () {
            if (count($this->newServices) > 0) {
                $this->createNewJobService->setJob($this->job)->setServices($this->newServices)->create();
            }
            if (count($this->materials) > 0) {
                $this->materialCreator->setJob($this->job)->setUserAgentInformation($this->userAgentInformation)
                    ->setMaterials($this->materials)->create();
            }
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

    public function updateMaterial(JobMaterial $jobMaterial, $new_name, $new_price)
    {
        if (!$this->canUpdate()) {
            $this->response->setCode(403)->setMessage('আপনার এই প্রক্রিয়া টি সম্পন্ন করা সম্ভব নয়, অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন');
            return $this->response;
        }
        DB::transaction(function () use ($jobMaterial, $new_name, $new_price) {
            $this->materialUpdater->setJob($this->job)->setUserAgentInformation($this->userAgentInformation)->setMaterialName($new_name)->setMaterialPrice($new_price)
                ->setJobMaterial($jobMaterial)->update();
            $this->response->setSuccessfulCode()->setSuccessfulMessage();
        });
        return $this->response;
    }

    private function canUpdate()
    {
        return $this->policy->setJob($this->job)->setPartner($this->job->partnerOrder->partner)->canUpdate();
    }
}
