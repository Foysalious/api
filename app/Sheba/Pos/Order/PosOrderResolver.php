<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use App\Sheba\PosOrderService\PosOrderServerClient;

class PosOrderResolver
{
    private $orderId;
    private $posOrderType;
    /**  @var PosOrderServerClient */
    private $client;

    public function __construct(PosOrderServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $orderId
     * @return PosOrderResolver
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function isNewSystemPosOrder()
    {
        return $this->posOrderType == PosOrderTypes::OLD_POS_ORDER ? false : true;
    }

    public function getPosOrderId()
    {
        $posOrder = PosOrder::find($this->orderId);
        if ($posOrder && !$posOrder->partner->isMigrationCompleted()) {
            $this->posOrderType = PosOrderTypes::OLD_POS_ORDER;
            return $posOrder->id;
        } else {
            $this->posOrderType = PosOrderTypes::NEW_POS_ORDER;
            $response = $this->client->get('api/v1/orders/' . $this->orderId);
            return $response->order_id;
        }
    }
}