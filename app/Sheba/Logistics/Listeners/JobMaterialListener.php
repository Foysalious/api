<?php namespace Sheba\Logistics\Listeners;

use Sheba\Dal\JobMaterial\Events\JobMaterialSaved;
use Sheba\Dal\JobMaterial\JobMaterial;
use Sheba\Logistics\Exceptions\LogisticServerError;

class JobMaterialListener extends BaseListener
{
    /**
     * @param JobMaterialSaved $event
     * @throws LogisticServerError
     */
    public function handle(JobMaterialSaved $event)
    {
        /** @var JobMaterial $job_material */
        $job_material = $event->model;
        $this->update($job_material->job->partnerOrder);
    }
}
