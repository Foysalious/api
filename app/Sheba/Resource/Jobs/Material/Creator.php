<?php namespace Sheba\Resource\Jobs\Material;


use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Sheba\Dal\JobMaterial\JobMaterialRepositoryInterface;
use Sheba\Dal\JobMaterialLog\JobMaterialLogRepositoryInterface;
use Sheba\UserAgentInformation;

class Creator
{
    /** @var Job */
    private $job;
    /** @var array */
    private $materials;
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

    /**
     * @param mixed $job
     * @return Creator
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param array $materials
     * @return Creator
     */
    public function setMaterials($materials)
    {
        $this->materials = $materials;
        return $this;
    }

    public function create()
    {
        foreach ($this->materials as $material) {
            $this->jobMaterialRepository->create([
                'material_name' => $material['name'],
                'material_price' => $material['price'],
                'job_id' => $this->job->id,
            ]);
            $this->jobMaterialLogRepository->create([
                'job_id' => $this->job->id,
                'log' => 'Job Material added at ' . Carbon::now()->toDateTimeString(),
                'portal_name' => $this->userAgentInformation->getPortalName(),
                'ip' => $this->userAgentInformation->getIp(),
                'user_agent' => $this->userAgentInformation->getUserAgent(),
                'created_by_type' => get_class(new Resource()),
                'new_data' => json_encode([
                    'material_name' => $material['name'],
                    'material_price' => $material['price']
                ])
            ]);
        }
    }
}