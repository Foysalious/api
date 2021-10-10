<?php namespace Sheba\Pos\Product\Listeners;


use App\Models\PartnerPosService;
use Sheba\Dal\Extras\Events\BaseEvent;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSavedEvent;
use Sheba\Dal\PartnerPosService\PartnerPosServiceRepository;
use Sheba\Subscription\Partner\Access\AccessManager;
use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;

class WebstorePublishCheck
{
    /** @var PartnerPosServiceRepository */
    private $serviceRepository;

    public function __construct(PartnerPosServiceRepository $service_repo)
    {
        $this->serviceRepository = $service_repo;
    }

    /**
     * @param PartnerPosServiceSavedEvent $event
     */
    public function handle(BaseEvent $event)
    {
        /** @var PartnerPosService $partner_pos_service */
        $partner_pos_service = $event->model;
        if (!$partner_pos_service->isWebstorePublished()) return;
        if (PartnerPosService::webstorePublishedServiceByPartner($partner_pos_service->partner_id)->count() < $partner_pos_service->partner->subscription->getAccessRules()['pos']['ecom']['product_publish_limit']) return;
        try {
            AccessManager::checkAccess(AccessManager::Rules()->POS->ECOM->PRODUCT_PUBLISH, $partner_pos_service->partner->subscription->getAccessRules());
        } catch (AccessRestrictedExceptionForPackage $e) {
            $this->serviceRepository->unpublishFromWebstore($partner_pos_service);
        }
    }
}