<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use App\Sheba\Pos\Order\Customer\CustomerObject;
use App\Sheba\Pos\Order\Partner\PartnerObject;
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
    /**
     * @var PartnerObject
     */
    private $partnerObject;

    public function __construct(PosOrderServerClient $client, PosOrderObject $posOrderObject, CustomerObject $customerObject, PartnerObject $partnerObject)
    {
        $this->client = $client;
        $this->posOrderObject = $posOrderObject;
        $this->customerObject = $customerObject;
        $this->partnerObject = $partnerObject;
    }

    /**
     * @param mixed $orderId
     * @return PosOrderResolver
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        $oldPosOrder = PosOrder::where('id', $orderId)->select('id', 'sales_channel', 'customer_id', 'partner_id', 'is_migrated', 'created_at')->first();
        if ($oldPosOrder && !$oldPosOrder->is_migrated) {
            $customer = $oldPosOrder->customer->profile;
            $partner = $oldPosOrder->partner;
            $partnerObject = $this->partnerObject->setId($partner->id)->setSubDomain($partner->sub_domain)->get();
            $customerObject = $this->customerObject->setId($customer->id)->setName($customer->name)->setMobile($customer->mobile)->get();
            $this->order = $this->posOrderObject->setId($oldPosOrder->id)->setSalesChannel($oldPosOrder->sales_channel)->setIsMigrated(0)
                ->setCustomer($customerObject)->setPartner($partnerObject)->setType(PosOrderTypes::OLD_SYSTEM)->setCreatedAt($oldPosOrder->created_at)->get();
        } else {
            $response = $this->client->get('api/v1/order-info-for-payment-link/' . $this->orderId);
            $newPosOrder = json_decode(json_encode($response['order']), FALSE);
            $customer = $newPosOrder->customer;
            $partner = $newPosOrder->partner;
            $partnerObject = $this->partnerObject->setId($partner->id)->setSubDomain($partner->sub_domain)->get();
            $customerObject = $this->customerObject->setId(null)->setName($customer->name)->setMobile($customer->mobile)->get();
            $this->order = $this->posOrderObject->setId($newPosOrder->id)->setSalesChannel($newPosOrder->sales_channel)
                ->setIsMigrated(1)->setCustomer($customerObject)->setPartner($partnerObject)->setType(PosOrderTypes::NEW_SYSTEM)
                ->setCreatedAt($newPosOrder->created_at)->get();
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
}