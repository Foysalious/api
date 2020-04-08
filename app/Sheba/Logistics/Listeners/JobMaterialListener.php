<?php namespace Sheba\Logistics\Listeners;


use Sheba\Dal\JobMaterial\Events\JobMaterialSaved;
use Sheba\Dal\JobMaterial\JobMaterial;
use Sheba\Logistics\UpdatePriceHandler;
use Sheba\Report\Listeners\BaseSavedListener;

class JobMaterialListener extends BaseSavedListener
{
    private $priceHandler;

    public function __construct(UpdatePriceHandler $priceHandler)
    {
        $this->priceHandler = $priceHandler;
    }

    public function handle(JobMaterialSaved $event)
    {
        /** @var JobMaterial $job_material */
        $job_material = $event->model;
        $this->priceHandler->setPartnerOrder($job_material->job->partnerOrder)->update();
    }
}