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

    /**
     * @param mixed $posOrderType
     * @return PosOrderResolver
     */
    public function setPosOrderType($posOrderType)
    {
        $this->posOrderType = $posOrderType;
        return $this;
    }

    public function getType()
    {
        return $this->posOrderType;
    }

    public function getPosOrder()
    {
        $posOrder = PosOrder::find($this->orderId);
        if ($posOrder) {
            $this->setPosOrderType(PosOrderTypes::OLD_POS_ORDER);
            return $posOrder;
        } else {
            return $this->client->get('api/v1/orders/' . $this->orderId);
        }
    }



}