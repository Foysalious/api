<?php namespace Sheba\Resource\Jobs\Material;


use App\Models\Job;
use Sheba\Dal\JobMaterial\JobMaterialRepositoryInterface;

class Creator
{
    /** @var Job */
    private $job;
    /** @var array */
    private $materials;
    private $jobMaterialRepository;

    public function __construct(JobMaterialRepositoryInterface $jobMaterialRepository)
    {
        $this->jobMaterialRepository = $jobMaterialRepository;
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
        }
    }
}