<?php namespace Sheba\Resource\Jobs\Service;

use Sheba\Dal\JobService\JobService;
use Sheba\Dal\JobService\JobServiceRepositoryInterface;
use Sheba\Dal\JobUpdateLog\JobUpdateLogRepositoryInterface;
class Updater
{
    /** @var JobService */
    private $jobService;
    /** @var float */
    private $quantity;
    private $unitPrice;
    /** @var JobServiceRepositoryInterface */
    private $jobServiceRepository;
    private $jobUpdateLogRepository;


    public function __construct(JobServiceRepositoryInterface $jobServiceRepository, JobUpdateLogRepositoryInterface $jobUpdateLogRepository)
    {

        $this->jobServiceRepository = $jobServiceRepository;
        $this->jobUpdateLogRepository = $jobUpdateLogRepository;
    }

    /**
     * @param JobService $jobService
     * @return Updater
     */
    public function setJobService($jobService)
    {
        $this->jobService = $jobService;
        return $this;
    }

    /**
     * @param float $quantity
     * @return Updater
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function update()
    {
        if ($this->quantity <= $this->jobService->quantity) return;
        $this->saveServicePriceUpdateLog();
        $this->jobServiceRepository->update($this->jobService, ['quantity' => $this->quantity]);
    }

    private function saveServicePriceUpdateLog()
    {
        $updated_data = [
            'msg' => 'Service Price Updated',
            'old_service_unit_price' => $this->jobService->unit_price,
            'old_service_quantity' => $this->jobService->quantity,
            'new_service_unit_price' => $this->unitPrice,
            'new_service_quantity' => $this->quantity,
            'service_name' => $this->jobService->name
        ];
        $this->jobUpdateLogRepository->create(['job_id' => $this->jobService->job_id, 'log' => json_encode($updated_data)]);
    }

}