<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use App\Sheba\Pos\Order\Customer\CustomerObject;
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
    /**
     * @var CustomerObject
     */
    private $customerObject;

    public function __construct(PosOrderServerClient $client, PosOrderObject $posOrderObject, CustomerObject $customerObject)
    {
        $this->client = $client;
        $this->posOrderObject = $posOrderObject;
        $this->customerObject = $customerObject;
    }

    /**
     * @param mixed $orderId
     * @return PosOrderResolver
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        $oldPosOrder = PosOrder::where('id', $orderId)->select('id', 'sales_channel', 'customer_id')->first();
        if ($oldPosOrder && !$oldPosOrder->is_migrated) {
            $customer = $oldPosOrder->customer->profile;
            $customerObject = $this->customerObject->setId($customer->id)->setName($customer->name)->setMobile($customer->mobile)->get();
            $this->order = $this->posOrderObject->setId($oldPosOrder->id)->setSalesChannel($oldPosOrder->sales_channel)->setCustomer($customerObject)->setType(PosOrderTypes::OLD_SYSTEM)->get();
        } else {
            $response = $this->client->get('api/v1/order-channel/' . $this->orderId);
            $newPosOrder = json_decode(json_encode($response['order']), FALSE);
            $customer = $newPosOrder->customer;
            $customerObject = $this->customerObject->setId(null)->setName($customer->name)->setMobile($customer->mobile)->get();
            $this->order = $this->posOrderObject->setId($newPosOrder->id)->setSalesChannel($newPosOrder->sales_channel)->setCustomer($customerObject)->setType(PosOrderTypes::NEW_SYSTEM)->get();
        }
        return $this;
    }

    /**
     * @return PosOrderObject
     */
    public function get()
    {
        return $this->order;
    }

    private function setCustomer()
    {
        /** @var CustomerObject $customerObject */
        $customerObject = app(CustomerObject::class);
        return $customerObject->setId()->setName()->setMobile()->get();
    }
}