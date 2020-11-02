<?php namespace App\Sheba\Algolia\Listeners;


use App\Models\PartnerPosService;
use App\Sheba\Algolia\Events\PartnerPosServiceCreated as PartnerPosServiceCreatedEvent;

class PartnerPosServiceCreated
{
    /**
     * @param PartnerPosServiceCreatedEvent $event
     */
    public function handle(PartnerPosServiceCreatedEvent $event)
    {
        /** @var PartnerPosService $partner_pos_service */
        $partner_pos_service = $event->newModel;
        $partner_pos_service->pushToIndex();
    }
}