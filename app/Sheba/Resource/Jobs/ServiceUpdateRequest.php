<?php namespace Sheba\Resource\Jobs;

use App\Models\Job;
use Sheba\Dal\JobService\JobService;
use Sheba\Resource\Jobs\Service\Creator;
use Sheba\Resource\Jobs\Service\Updater;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\Resource\Jobs\Material\Creator as MaterialCreator;
use DB;

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

    public function __construct(ServiceRequest $serviceRequest, Creator $create_new_job_service, MaterialCreator $material_creator, Updater $updater)
    {
        $this->serviceRequest = $serviceRequest;
        $this->createNewJobService = $create_new_job_service;
        $this->materialCreator = $material_creator;
        $this->updater = $updater;
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
     * @throws \Sheba\ServiceRequest\ServiceIsUnpublishedException
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

    public function update()
    {
        DB::transaction(function () {
//            if (count($this->newServices) > 0) $this->createNewJobService->setJob($this->job)->setServices($this->newServices)->create();
//            if (count($this->materials) > 0) $this->materialCreator->setJob($this->job)->setMaterials($this->materials)->create();
            foreach ($this->quantity as $quantity) {
                $job_service = JobService::find($quantity['job_service_id']);
                $this->updater->setJobService($job_service)->setQuantity($quantity['quantity'])->update();
            }
        });

    }


}