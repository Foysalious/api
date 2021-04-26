<?php namespace App\Sheba\Algolia\Listeners;


use App\Models\PartnerPosService;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSavedEvent;
use Throwable;

class PartnerPosServiceSaved
{
    /**
     * @param PartnerPosServiceSavedEvent $event
     */
    public function handle(PartnerPosServiceSavedEvent $event)
    {
        /** @var PartnerPosService $partner_pos_service */
        $partner_pos_service = $event->model;
        try {
            $partner_pos_service->addToIndex();
        } catch (Throwable $e) {

        }
    }

}