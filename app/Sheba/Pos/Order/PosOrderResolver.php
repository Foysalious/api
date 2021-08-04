<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use App\Sheba\PosOrderService\PosOrderServerClient;

class PosOrderResolver
{
    private $orderId;
    /**  @var PosOrderServerClient */
    private $client;
    private $order;
    private $posOrderType;

    public function __construct(PosOrderServerClient $client)
    {
        $this->client = $client;
        $this->posOrderType = PosOrderTypes::OLD_SYSTEM;
    }

    /**
     * @param mixed $orderId
     * @return PosOrderResolver
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        $oldPosOrder = PosOrder::find($orderId);
        if ($oldPosOrder && !$oldPosOrder->is_migrated) {
            $this->order = $oldPosOrder;
            $this->posOrderType = PosOrderTypes::OLD_SYSTEM;
        } else {
            $response = $this->client->get('api/v1/order-channel/' . $this->orderId);
            $this->order = json_decode(json_encode($response['order']), FALSE);
            $this->posOrderType = PosOrderTypes::NEW_SYSTEM;
        }
        return $this;
    }

    public function get()
    {
        return $this->order;
    }

    public function getPosOrderType()
    {
        return $this->posOrderType;
    }
}