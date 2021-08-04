<?php namespace App\Sheba\Algolia\Listeners;

use App\Models\Partner;
use Sheba\Dal\Partner\Events\PartnerSaved as PartnerSavedEvent;

class PartnerSaved
{

    public function handle(PartnerSavedEvent $event)
    {
        /** @var Partner $partner */
        $partner = $event->model;
        if ($partner->is_webstore_published == 1)
            $partner->pushToIndex();
        if ($partner->is_webstore_published == 0)
            $partner->removeFromIndex();
    }

}