<?php namespace Sheba\Logistics\LogisticsNatures;

use Carbon\Carbon;

class TwoWayLogisticLastOrder extends TwoWayLogistic
{
    /**
     * @return Carbon
     */
    public function getPickupTime()
    {
        return Carbon::now();
    }

    public function isInstant()
    {
        return true;
    }

    public function getCollectableAmount()
    {
        return parent::getCollectableAmount() + ($this->deliveryCharge - $this->getFirstOrderPaid());
    }

    private function getFirstOrderPaid()
    {
        return parent::getPaidAmount();
    }
}