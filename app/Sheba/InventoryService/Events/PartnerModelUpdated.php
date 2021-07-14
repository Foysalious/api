<?php namespace App\Sheba\InventoryService\Events;

use App\Events\Event;
use App\Models\Partner;
use Illuminate\Queue\SerializesModels;

class PartnerModelUpdated extends Event
{
     use SerializesModels;
     protected $partner;

     public function __construct(Partner $partner)
     {
         $this->partner = $partner;
     }

    /**
     * @return Partner
     */
    public function getModel()
    {
        return $this->partner;

    }
}