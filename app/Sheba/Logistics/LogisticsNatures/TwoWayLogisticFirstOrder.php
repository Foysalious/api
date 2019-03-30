<?php namespace Sheba\Logistics\LogisticsNatures;

use Sheba\Logistics\DTO\Point;

class TwoWayLogisticFirstOrder extends TwoWayLogistic
{

    /**
     * @return Point
     */
    public function getPickUp()
    {
        return $this->getCustomerPoint();
    }

    /**
     * @return Point
     */
    public function getDropOff()
    {
        return $this->getPartnerPoint();
    }

    public function getCollectableAmount()
    {
        return 0.00;
    }

    public function getDiscount()
    {
        return ['amount' => $this->deliveryCharge - $this->getPaidAmount(), 'is_percentage' => false];
    }
}