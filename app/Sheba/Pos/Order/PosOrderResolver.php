<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use App\Sheba\PosOrderService\PosOrderServerClient;

class PosOrderResolver
{
    private $orderId;
    private $posOrderType;
    /**  @var PosOrderServerClient */
    private $client;
    /** @var \Sheba\Pos\Order\PosOrder */
    private $posOrder;
    private $order;

    public function __construct(PosOrderServerClient $client, \Sheba\Pos\Order\PosOrder $posOrder)
    {
        $this->client = $client;
        $this->posOrder = $posOrder;
    }

    /**
     * @param mixed $orderId
     * @return PosOrderResolver
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        $oldPosOrder = PosOrder::where('id', $orderId)->select('id', 'sales_channel', 'partner_id')->first();
        if ($oldPosOrder && !$oldPosOrder->partner->isMigrationCompleted()) {
            $this->order = $oldPosOrder;
            $this->order->order_type = PosOrderTypes::OLD_POS_ORDER;
        } else {
            $response = $this->client->get('api/v1/order-channel/' . $this->orderId);
            $this->order = json_decode(json_encode($response['order']), FALSE);
            $this->order->order_type = PosOrderTypes::NEW_POS_ORDER;
        }
        return $this;
    }

    public function get()
    {
        return $this->order;
    }

    public function isNewSystemPosOrder()
    {
        return $this->order->order_type == PosOrderTypes::OLD_POS_ORDER ? false : true;
    }
}