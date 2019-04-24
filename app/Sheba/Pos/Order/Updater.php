<?php namespace Sheba\Pos\Order;

use App\Models\PosOrder;
use Sheba\Pos\Order\RefundNatures\NatureFactory;
use Sheba\Pos\Order\RefundNatures\Natures;

class Updater
{
    /**
     * @var PosOrder
     */
    private $order;
    /**
     * @var array
     */
    private $data;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function update()
    {
        $refund_nature = ($this->isReturned()) ? Natures::RETURNED : Natures::EXCHANGED;
        $refund_nature = NatureFactory::getRefundNature($this->order, $refund_nature);

        return;
    }

    private function isReturned()
    {
        $services = $this->order->items->pluck('service_id')->toArray();
        $request_services = collect(json_decode($this->data['services'], true))->pluck('id')->toArray();

        return $services === $request_services;
    }
}