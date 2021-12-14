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
     * @return PosOrderResolver|null
     */
    public function setOrderId($orderId)
    {
        $oldPosOrder = PosOrder::where('id', $orderId)->select('id', 'sales_channel', 'customer_id', 'partner_id', 'is_migrated', 'created_at', 'emi_month')->first();
        if ($oldPosOrder && !$oldPosOrder->is_migrated) $this->setOldPosOrderObject($oldPosOrder);
        else $this->setNewPosOrderObject();
        return $this;
    }

    public function setPartnerWiseOrderId($partnerId, $partnerWiseOrderId)
    {
        $oldPosOrder = PosOrder::where('partner_wise_order_id', $partnerWiseOrderId)->where('partner_id', $partnerId)->select('id', 'sales_channel', 'customer_id', 'partner_id', 'is_migrated', 'created_at', 'emi_month')->first();
        if ($oldPosOrder && !$oldPosOrder->is_migrated) $this->setOldPosOrderObject($oldPosOrder);
        else $this->setNewPosOrderObject();
        return $this;
    }

    private function setOldPosOrderObject($oldPosOrder)
    {
        $this->orderId = $oldPosOrder->id;
        $oldPosOrder->calculate();
        $customer = $oldPosOrder->customer;
        $partner = $oldPosOrder->partner;
        $partnerObject = $this->partnerObject->setId($partner->id)->setSubDomain($partner->sub_domain);
        if ($oldPosOrder->customer_id) {
            $customerObject = $this->customerObject->setId($customer->id)->setName($customer->nicak_name ?: $customer->profile->name)->setMobile($customer->profile->mobile);
        } else {
            $customerObject = null;
        }
        $this->posOrderObject->setId($oldPosOrder->id)->setCustomerId($oldPosOrder->customer_id)->setPartnerId($oldPosOrder->partner_id)
            ->setDue($oldPosOrder->getDue())->setSalesChannel($oldPosOrder->sales_channel)->setIsMigrated(0)
            ->setCustomer($customerObject)->setPartner($partnerObject)->setType(PosOrderTypes::OLD_SYSTEM)
            ->setCreatedAt($oldPosOrder->created_at)->setEmiMonth($oldPosOrder->emi_month);
        $this->setOrder($this->posOrderObject);
    }

    private function setNewPosOrderObject()
    {
        $response = $this->client->get('api/v1/orders/' . $this->orderId);
        $newPosOrder = json_decode(json_encode($response['order']), FALSE);
        $this->orderId = $newPosOrder->id;
        $customer = $newPosOrder->customer;
        $partner = $newPosOrder->partner;
        $partnerObject = $this->partnerObject->setId($partner->id)->setSubDomain($partner->sub_domain);
        if ($newPosOrder->customer_id) {
            $customerObject = $this->customerObject->setId($customer->id)->setName($customer->name)->setMobile($customer->mobile);
        } else {
            $customerObject = null;
        }
        $this->posOrderObject->setId($newPosOrder->id)->setCustomerId($newPosOrder->customer_id)->setPartnerId($newPosOrder->partner_id)
            ->setDue($newPosOrder->due)->setSalesChannel($newPosOrder->sales_channel)->setIsMigrated(1)
            ->setCustomer($customerObject)->setPartner($partnerObject)->setType(PosOrderTypes::NEW_SYSTEM)
            ->setCreatedAt($newPosOrder->created_at)->setEmiMonth($newPosOrder->emi_month);
        $this->setOrder($this->posOrderObject);
    }

    public function get(): PosOrderObject
    {
        return $this->order;
    }

    /**
     * @param PosOrderObject $order
     * @return void
     */
    private function setOrder(PosOrderObject $order)
    {
        $this->order = $order;
    }
}
