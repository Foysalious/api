<?php namespace Sheba\Order;


use App\Models\Order;

class StatusCalculator
{
    private $order;
    private $partnerOrderStatusCounter;
    private $orderStatuses;
    private $partnerOrderStatuses;
    private $partnerOrderCounter;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->orderStatuses = constants('ORDER_STATUSES');
        $this->partnerOrderStatuses = constants('PARTNER_ORDER_STATUSES');
        $this->partnerOrderStatusCounter = [
            $this->partnerOrderStatuses['Open'] => 0,
            $this->partnerOrderStatuses['Process'] => 0,
            $this->partnerOrderStatuses['Closed'] => 0,
            $this->partnerOrderStatuses['Cancelled'] => 0
        ];
        $this->partnerOrderCounter = 0;
    }

    public function calculate()
    {
        foreach($this->order->partner_orders as $partnerOrder) {
            $partner_order_status = property_exists($partnerOrder, 'status') && $partnerOrder->status ? $partnerOrder->status : $partnerOrder->getStatus();
            $this->partnerOrderStatusCounter[$partner_order_status]++;
            $this->partnerOrderCounter++;
        }

        if($this->isAllPartnerOrderCancelled()) {
            return $this->orderStatuses['Cancelled'];
        } else if($this->isAllPartnerOrderOpen()) {
            return $this->orderStatuses['Open'];
        } else if($this->isAllPartnerOrderClosed()) {
            return $this->orderStatuses['Closed'];
        } else {
            return $this->orderStatuses['Process'];
        }
    }

    private function isAllPartnerOrderCancelled()
    {
        return $this->partnerOrderStatusCounter[$this->partnerOrderStatuses['Cancelled']] == $this->partnerOrderCounter;
    }

    private function isAllPartnerOrderOpen()
    {
        $open_partner_order = $this->partnerOrderStatusCounter[$this->partnerOrderStatuses['Open']];
        $cancelled_partner_order = $this->partnerOrderStatusCounter[$this->partnerOrderStatuses['Cancelled']];
        return ($open_partner_order + $cancelled_partner_order) == $this->partnerOrderCounter;
    }

    private function isAllPartnerOrderClosed()
    {
        $closed_partner_order = $this->partnerOrderStatusCounter[$this->partnerOrderStatuses['Closed']];
        $cancelled_partner_order = $this->partnerOrderStatusCounter[$this->partnerOrderStatuses['Cancelled']];
        return ($closed_partner_order + $cancelled_partner_order) == $this->partnerOrderCounter;
    }
    
}