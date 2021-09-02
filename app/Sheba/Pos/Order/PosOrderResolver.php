<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use App\Sheba\Pos\Order\PosOrderObject;
use App\Sheba\PosOrderService\PosOrderServerClient;

class PosOrderResolver
{
    private $orderId;
    /**  @var PosOrderServerClient */
    private $client;
    /** @var PosOrderObject $order */
    private $order;
    /** @var PosOrderObject */
    private $posOrderObject;

    public function __construct(PosOrderServerClient $client, PosOrderObject $posOrderObject)
    {
        $this->client = $client;
        $this->posOrderObject = $posOrderObject;
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
            $this->order = $this->posOrderObject->setId($oldPosOrder->id)->setSalesChannel($oldPosOrder->sales_channel)->setType(PosOrderTypes::OLD_SYSTEM)->get();
        } else {
            $response = $this->client->get('api/v1/order-channel/' . $this->orderId);
            $newPosOrder = json_decode(json_encode($response['order']), FALSE);
            $this->order = $this->posOrderObject->setId($newPosOrder->id)->setSalesChannel($newPosOrder->sales_channel)->setType(PosOrderTypes::NEW_SYSTEM)->get();
        }
        return $this;
    }

    public function get()
    {
        return $this->order;
    }
}