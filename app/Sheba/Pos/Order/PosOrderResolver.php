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
    protected $order;
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
            $oldPosOrder->calculate();
            $customer = $oldPosOrder->customer->profile;
            $partner = $oldPosOrder->partner;
            $partnerObject = $this->partnerObject->setId($partner->id)->setSubDomain($partner->sub_domain);
            $customerObject = $this->customerObject->setId($customer->id)->setName($customer->name)->setMobile($customer->mobile);
            $this->order = $this->posOrderObject->setId($oldPosOrder->id)->setCustomerId($oldPosOrder->customer_id)->setPartnerId($oldPosOrder->partner_id)
                ->setDue($oldPosOrder->getDue())->setSalesChannel($oldPosOrder->sales_channel)->setIsMigrated(0)
                ->setCustomer($customerObject)->setPartner($partnerObject)->setType(PosOrderTypes::OLD_SYSTEM)
                ->setCreatedAt($oldPosOrder->created_at);
        } else {
            $response = $this->client->get('api/v1/orders/' . $this->orderId);
            $newPosOrder = json_decode(json_encode($response['order']), FALSE);
            $customer = $newPosOrder->customer;
            $partner = $newPosOrder->partner;
            $partnerObject = $this->partnerObject->setId($partner->id)->setSubDomain($partner->sub_domain);
            $customerObject = $this->customerObject->setId($customer->id)->setName($customer->name)->setMobile($customer->mobile);
            $this->order = $this->posOrderObject->setId($newPosOrder->id)->setCustomerId($newPosOrder->customer_id)->setPartnerId($newPosOrder->partner_id)
                ->setDue($newPosOrder->due)->setSalesChannel($newPosOrder->sales_channel)->setIsMigrated(1)
                ->setCustomer($customerObject)->setPartner($partnerObject)->setType(PosOrderTypes::NEW_SYSTEM)
                ->setCreatedAt($newPosOrder->created_at);
        }
        return $this;
    }

    public function get(): PosOrderObject
    {
        return $this->order;
    }
}