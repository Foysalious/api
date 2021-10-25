<?php namespace Sheba\Algolia\Listeners;

use Sheba\Dal\Extras\Events\BaseEvent;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSavedEvent;

class PosServiceSavedListener
{
    /**
     * @param PartnerPosServiceSavedEvent $event
     */
    public function handle(BaseEvent $event)
    {
//        /** @var PartnerPosService $partner_pos_service */
//        $partner_pos_service = $event->model;
//        if ($partner_pos_service->trashed() || !$partner_pos_service->isWebstorePublished()) {
//            $partner_pos_service->removeFromIndex();
//        } else {
//            $partner_pos_service->addToIndex();
//        }
    }
}
