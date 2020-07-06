<?php namespace Sheba\Resource\Jobs\Material;


use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Sheba\Dal\JobMaterial\JobMaterial;
use Sheba\Dal\JobMaterial\JobMaterialRepositoryInterface;
use Sheba\Dal\JobMaterialLog\JobMaterialLogRepositoryInterface;
use Sheba\UserAgentInformation;

class Updater
{
    /** @var JobMaterial */
    private $jobMaterial;
    /** @var Job */
    private $job;
    /** @var array */
    private $materialName;
    private $materialPrice;
    private $jobMaterialRepository;
    private $jobMaterialLogRepository;
    /** @var UserAgentInformation */
    private $userAgentInformation;

    public function __construct(JobMaterialRepositoryInterface $jobMaterialRepository, JobMaterialLogRepositoryInterface $jobMaterialLogRepository)
    {
        $this->jobMaterialRepository = $jobMaterialRepository;
        $this->jobMaterialLogRepository = $jobMaterialLogRepository;
    }

    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }


    public function setJobMaterial(JobMaterial $job_material)
    {
        $this->jobMaterial = $job_material;
        return $this;
    }

    public function setMaterialName($materialName)
    {
        $this->materialName = $materialName;
        return $this;
    }

    public function setMaterialPrice($materialPrice)
    {
        $this->materialPrice = $materialPrice;
        return $this;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function update()
    {
        $this->jobMaterialLogRepository->create([
            'job_id' => $this->job->id,
            'log' => 'Job Material updated at ' . Carbon::now()->toDateTimeString(),
            'portal_name' => $this->userAgentInformation->getPortalName(),
            'ip' => $this->userAgentInformation->getIp(),
            'user_agent' => $this->userAgentInformation->getUserAgent(),
            'created_by_type' => get_class(new Resource()),
            'old_data' => json_encode([
                'material_name' => $this->jobMaterial->material_name,
                'material_price' => $this->jobMaterial->material_price
            ]),
            'new_data' => json_encode([
                'material_name' => $this->materialName,
                'material_price' => $this->materialPrice
            ])
        ]);
        $this->jobMaterialRepository->update($this->jobMaterial, [
            'material_name' => $this->materialName,
            'material_price' => $this->materialPrice,
            'job_id' => $this->job->id,
        ]);
    }
}