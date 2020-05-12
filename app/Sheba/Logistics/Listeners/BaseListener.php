<?php namespace Sheba\Logistics\Listeners;

use App\Models\PartnerOrder;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\UpdatePriceHandler;

abstract class BaseListener
{
    /** @var UpdatePriceHandler */
    protected $priceHandler;

    public function __construct(UpdatePriceHandler $priceHandler)
    {
        $this->priceHandler = $priceHandler;
    }

    /**
     * @param PartnerOrder $partner_order
     * @throws LogisticServerError
     */
    protected function update(PartnerOrder $partner_order)
    {
        $this->priceHandler->setPartnerOrder($partner_order)->update();
    }
}
