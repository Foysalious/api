<?php namespace Sheba\AutoSpAssign;


use App\Models\Customer;
use App\Models\Order;
use App\Models\PartnerOrder;
use Sheba\AutoSpAssign\Sorting\PartnerSort;
use Sheba\AutoSpAssign\Sorting\Strategy\Basic;
use Sheba\AutoSpAssign\Sorting\Strategy\Best;
use Sheba\PartnerOrderRequest\Creator;
use Sheba\PartnerOrderRequest\Store;

class Initiator
{
    /** @var Customer */
    private $customer;
    /** @var PartnerOrder */
    private $partnerOrder;
    /** @var array */
    private $partnerIds;
    /** @var Creator */
    private $partnerOrderRequestCreator;
    /** @var Store */
    private $orderRequestStore;

    public function __construct(Store $order_request_store, Creator $creator)
    {
        $this->orderRequestStore = $order_request_store;
        $this->partnerOrderRequestCreator = $creator;
    }

    /**
     * @param array $partnerIds
     * @return Initiator
     */
    public function setPartnerIds($partnerIds)
    {
        $this->partnerIds = $partnerIds;
        return $this;
    }

    /**
     * @param Customer $customer
     * @return Initiator
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param PartnerOrder $partnerOrder
     * @return Initiator
     */
    public function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        return $this;
    }

    public function initiate()
    {
        $finder = new Finder();
        $eligible_partners = $finder->setPartnerIds($this->partnerIds)->setCategoryId($this->partnerOrder->jobs->first()->category_id)->find();
        $sorter = new PartnerSort();
        $eligible_partners = $sorter->setStrategy($this->getStrategy())->sort($eligible_partners);
        dd($eligible_partners);
//        $this->orderRequestStore->setPartnerOrderId($this->partnerOrder->id)->setPartners($partners->pluck('id')->values()->all())->set();
//        $first_partner_id = [$partners->first()->id];
//        $this->partnerOrderRequestCreator->setPartnerOrder($partner_order)->setPartners($first_partner_id)->create();
    }

    public
    function getStrategy()
    {
        if ($this->getCustomerOrderCount() < 3) return new Best();
        return new Basic();
    }

    /**
     * @return mixed
     */
    private
    function getCustomerOrderCount()
    {
        return 0;
        return Order::where('customer_id', $this->order->customer_id)->select('id')->count();
    }


}