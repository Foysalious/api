<?php namespace App\Sheba\InventoryService\Partner\Events;

use App\Models\Partner;

class Created
{
    protected $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }


    /**
     * @return Partner
     */
    public function getModel(): Partner
    {
        return $this->partner;
    }
}